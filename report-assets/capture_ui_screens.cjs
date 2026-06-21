const fs = require("node:fs");
const path = require("node:path");
const { spawn } = require("node:child_process");

const project = "C:\\xampp\\htdocs\\hotel-booking-travel-mate-leaflet-map-select-result";
const outDir = path.join(project, "report-assets", "full-ui-screens");
const chromePath = "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe";
const baseUrl = "http://127.0.0.1:8000";

fs.mkdirSync(outDir, { recursive: true });

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

async function waitForJson(url, timeoutMs = 10000) {
  const started = Date.now();
  while (Date.now() - started < timeoutMs) {
    try {
      const res = await fetch(url);
      if (res.ok) return await res.json();
    } catch {}
    await sleep(150);
  }
  throw new Error(`Timed out waiting for ${url}`);
}

class CdpClient {
  constructor(wsUrl) {
    this.wsUrl = wsUrl;
    this.nextId = 1;
    this.pending = new Map();
    this.listeners = new Map();
  }

  async connect() {
    this.ws = new WebSocket(this.wsUrl);
    await new Promise((resolve, reject) => {
      this.ws.addEventListener("open", resolve, { once: true });
      this.ws.addEventListener("error", reject, { once: true });
    });
    this.ws.addEventListener("message", (event) => {
      const msg = JSON.parse(event.data);
      if (msg.id && this.pending.has(msg.id)) {
        const { resolve, reject } = this.pending.get(msg.id);
        this.pending.delete(msg.id);
        if (msg.error) reject(new Error(`${msg.error.message || "CDP error"}`));
        else resolve(msg.result || {});
        return;
      }
      if (msg.method && this.listeners.has(msg.method)) {
        for (const listener of this.listeners.get(msg.method)) listener(msg.params || {});
      }
    });
  }

  send(method, params = {}) {
    const id = this.nextId++;
    this.ws.send(JSON.stringify({ id, method, params }));
    return new Promise((resolve, reject) => {
      this.pending.set(id, { resolve, reject });
      setTimeout(() => {
        if (this.pending.has(id)) {
          this.pending.delete(id);
          reject(new Error(`CDP timeout: ${method}`));
        }
      }, 15000);
    });
  }

  close() {
    try {
      this.ws.close();
    } catch {}
  }
}

async function launchBrowser(profileName, port) {
  const profileDir = path.join(project, "report-assets", `chrome-profile-${profileName}`);
  fs.rmSync(profileDir, { recursive: true, force: true });
  fs.mkdirSync(profileDir, { recursive: true });
  const args = [
    "--headless=new",
    "--disable-gpu",
    "--hide-scrollbars",
    "--no-first-run",
    "--disable-extensions",
    "--disable-dev-shm-usage",
    `--remote-debugging-port=${port}`,
    `--user-data-dir=${profileDir}`,
    "--window-size=1366,1050",
    "about:blank",
  ];
  const proc = spawn(chromePath, args, { stdio: "ignore" });
  await waitForJson(`http://127.0.0.1:${port}/json/version`);
  const targets = await waitForJson(`http://127.0.0.1:${port}/json/list`);
  const page = targets.find((target) => target.type === "page") || targets[0];
  const client = new CdpClient(page.webSocketDebuggerUrl);
  await client.connect();
  await client.send("Page.enable");
  await client.send("Runtime.enable");
  await client.send("Network.enable");
  await client.send("Emulation.setDeviceMetricsOverride", {
    width: 1366,
    height: 1050,
    deviceScaleFactor: 1,
    mobile: false,
  });
  return { proc, client };
}

async function navigate(client, route, waitMs = 1800) {
  const url = route.startsWith("http") ? route : `${baseUrl}${route}`;
  await client.send("Page.navigate", { url });
  await sleep(waitMs);
  await client.send("Runtime.evaluate", {
    expression: "window.scrollTo(0, 0);",
    awaitPromise: false,
  });
  await sleep(300);
}

async function getPageInfo(client) {
  const result = await client.send("Runtime.evaluate", {
    expression: "JSON.stringify({title: document.title, url: location.href, body: document.body ? document.body.innerText.slice(0, 160) : ''})",
    returnByValue: true,
  });
  return JSON.parse(result.result.value || "{}");
}

async function login(client, email, password = "12345678") {
  await navigate(client, "/login", 1200);
  await client.send("Runtime.evaluate", {
    expression: `
      document.querySelector('input[name="email"]').value = ${JSON.stringify(email)};
      document.querySelector('input[name="password"]').value = ${JSON.stringify(password)};
      document.querySelector('form').submit();
    `,
    awaitPromise: false,
  });
  await sleep(2200);
}

async function screenshot(client, item, role) {
  await navigate(client, item.route, item.waitMs || 2000);
  const info = await getPageInfo(client);
  const data = await client.send("Page.captureScreenshot", {
    format: "png",
    captureBeyondViewport: false,
    fromSurface: true,
  });
  const file = path.join(outDir, `${item.file}.png`);
  fs.writeFileSync(file, Buffer.from(data.data, "base64"));
  return {
    role,
    file,
    fileName: `${item.file}.png`,
    title: item.title,
    caption: item.caption,
    route: item.route,
    currentUrl: info.url,
    pageTitle: info.title,
    bodyStart: info.body,
  };
}

const publicPages = [
  { file: "public-01-welcome", route: "/", title: "Trang chủ", caption: "Giao diện landing page Travel Mate" },
  { file: "public-02-hotels", route: "/hotels", title: "Danh sách khách sạn", caption: "Giao diện tìm kiếm và lọc khách sạn công khai" },
  { file: "public-03-hotel-detail", route: "/hotels/16?checkin_date=2026-06-07&checkout_date=2026-06-08&guests=1", title: "Chi tiết khách sạn", caption: "Giao diện chi tiết khách sạn công khai" },
  { file: "auth-01-login", route: "/login", title: "Đăng nhập", caption: "Giao diện đăng nhập" },
  { file: "auth-02-register", route: "/register", title: "Đăng ký", caption: "Giao diện đăng ký tài khoản" },
  { file: "auth-03-forgot-password", route: "/forgot-password", title: "Quên mật khẩu", caption: "Giao diện khôi phục mật khẩu" },
];

const customerPages = [
  { file: "customer-01-home", route: "/home", title: "Trang khách hàng", caption: "Giao diện trang chủ sau khi khách hàng đăng nhập" },
  { file: "customer-02-hotels", route: "/hotels", title: "Tìm khách sạn", caption: "Giao diện tìm khách sạn dành cho khách hàng" },
  { file: "customer-03-hotel-detail", route: "/hotels/16?checkin_date=2026-06-07&checkout_date=2026-06-08&guests=1", title: "Chi tiết khách sạn", caption: "Giao diện chi tiết khách sạn với thao tác đặt phòng" },
  { file: "customer-04-booking-history", route: "/my/bookings", title: "Lịch sử đặt phòng", caption: "Giao diện lịch sử đặt phòng của khách hàng" },
  { file: "customer-05-booking-detail", route: "/my/bookings/16", title: "Chi tiết booking", caption: "Giao diện chi tiết booking và hỗ trợ hủy/hoàn tiền" },
  { file: "customer-06-payment-checkout", route: "/payments/34/checkout", title: "Thanh toán", caption: "Giao diện thanh toán VNPAY sandbox và thanh toán giả lập" },
  { file: "customer-07-partner-request", route: "/partner-request/create", title: "Yêu cầu đối tác", caption: "Giao diện gửi yêu cầu trở thành đối tác" },
  { file: "customer-08-profile", route: "/profile", title: "Hồ sơ cá nhân", caption: "Giao diện cập nhật hồ sơ và mật khẩu" },
];

const ownerPages = [
  { file: "owner-01-dashboard", route: "/owner/dashboard", title: "Owner dashboard", caption: "Giao diện dashboard của chủ khách sạn" },
  { file: "owner-02-hotels-index", route: "/owner/hotels", title: "Quản lý khách sạn", caption: "Giao diện danh sách khách sạn của Owner" },
  { file: "owner-03-hotel-show", route: "/owner/hotels/16", title: "Chi tiết khách sạn Owner", caption: "Giao diện chi tiết khách sạn trong khu vực Owner" },
  { file: "owner-04-hotel-edit", route: "/owner/hotels/16/edit", title: "Sửa khách sạn", caption: "Giao diện chỉnh sửa thông tin khách sạn" },
  { file: "owner-05-hotel-create", route: "/owner/hotels/create", title: "Thêm khách sạn", caption: "Giao diện tạo khách sạn mới" },
  { file: "owner-06-room-types-index", route: "/owner/room-types", title: "Quản lý hạng phòng", caption: "Giao diện danh sách hạng phòng" },
  { file: "owner-07-room-type-show", route: "/owner/room-types/48", title: "Chi tiết hạng phòng", caption: "Giao diện chi tiết hạng phòng" },
  { file: "owner-08-room-type-edit", route: "/owner/room-types/48/edit", title: "Sửa hạng phòng", caption: "Giao diện chỉnh sửa hạng phòng" },
  { file: "owner-09-room-type-create", route: "/owner/room-types/create", title: "Thêm hạng phòng", caption: "Giao diện tạo hạng phòng mới" },
  { file: "owner-10-rooms-index", route: "/owner/rooms", title: "Quản lý phòng vật lý", caption: "Giao diện danh sách phòng vật lý" },
  { file: "owner-11-room-show", route: "/owner/rooms/288", title: "Chi tiết phòng", caption: "Giao diện chi tiết phòng vật lý" },
  { file: "owner-12-room-edit", route: "/owner/rooms/288/edit", title: "Sửa phòng", caption: "Giao diện chỉnh sửa phòng vật lý" },
  { file: "owner-13-room-create", route: "/owner/rooms/create", title: "Thêm phòng", caption: "Giao diện tạo phòng vật lý" },
  { file: "owner-14-bookings-index", route: "/owner/bookings", title: "Quản lý booking", caption: "Giao diện danh sách booking Owner" },
  { file: "owner-15-booking-show", route: "/owner/bookings/33", title: "Chi tiết booking Owner", caption: "Giao diện chi tiết booking trong vận hành khách sạn" },
  { file: "owner-16-check-in", route: "/owner/bookings/33/check-in", title: "Check-in", caption: "Giao diện check-in và gán phòng" },
  { file: "owner-17-change-room", route: "/owner/bookings/33/change-room", title: "Đổi phòng", caption: "Giao diện đổi phòng cho booking" },
  { file: "owner-18-revenues", route: "/owner/revenues", title: "Doanh thu", caption: "Giao diện doanh thu và khoản khấu trừ Owner" },
  { file: "owner-19-reviews", route: "/owner/reviews", title: "Đánh giá", caption: "Giao diện quản lý phản hồi đánh giá" },
];

const adminPages = [
  { file: "admin-01-dashboard", route: "/admin/dashboard", title: "Admin dashboard", caption: "Giao diện dashboard quản trị" },
  { file: "admin-02-users-index", route: "/admin/users", title: "Quản lý người dùng", caption: "Giao diện danh sách người dùng" },
  { file: "admin-03-user-show", route: "/admin/users/8", title: "Chi tiết người dùng", caption: "Giao diện chi tiết tài khoản người dùng" },
  { file: "admin-04-user-lock", route: "/admin/users/8/lock", title: "Khóa tài khoản", caption: "Giao diện xác nhận khóa tài khoản" },
  { file: "admin-05-partner-requests", route: "/admin/partner-requests", title: "Yêu cầu đối tác", caption: "Giao diện danh sách yêu cầu đối tác" },
  { file: "admin-06-partner-request-show", route: "/admin/partner-requests/4", title: "Chi tiết yêu cầu đối tác", caption: "Giao diện duyệt yêu cầu đối tác" },
  { file: "admin-07-hotels-index", route: "/admin/hotels", title: "Kiểm duyệt khách sạn", caption: "Giao diện danh sách khách sạn cần kiểm duyệt" },
  { file: "admin-08-hotel-show", route: "/admin/hotels/16", title: "Chi tiết khách sạn Admin", caption: "Giao diện chi tiết khách sạn cho Admin" },
  { file: "admin-09-hotel-status", route: "/admin/hotels/16/status", title: "Cập nhật trạng thái khách sạn", caption: "Giao diện cập nhật trạng thái kiểm duyệt khách sạn" },
  { file: "admin-10-refunds-index", route: "/admin/refunds", title: "Quản lý hoàn tiền", caption: "Giao diện danh sách yêu cầu hoàn tiền" },
  { file: "admin-11-refund-show", route: "/admin/refunds/22", title: "Xử lý hoàn tiền", caption: "Giao diện xử lý hoàn tiền và cảnh báo clawback" },
  { file: "admin-12-settlements-index", route: "/admin/settlements", title: "Đối soát", caption: "Giao diện danh sách giao dịch đối soát" },
  { file: "admin-13-settlement-show", route: "/admin/settlements/23", title: "Chi tiết đối soát", caption: "Giao diện xác nhận settlement và khấu trừ điều chỉnh" },
  { file: "admin-14-reviews", route: "/admin/reviews", title: "Kiểm duyệt đánh giá", caption: "Giao diện kiểm duyệt đánh giá" },
  { file: "admin-15-hotel-appeals", route: "/admin/hotel-appeals", title: "Khiếu nại khách sạn", caption: "Giao diện danh sách khiếu nại trạng thái khách sạn" },
];

async function captureGroup(role, pages, port, loginEmail = null) {
  const { proc, client } = await launchBrowser(role, port);
  const results = [];
  try {
    if (loginEmail) await login(client, loginEmail);
    for (const page of pages) {
      try {
        const result = await screenshot(client, page, role);
        console.log(`[${role}] ${page.file} -> ${result.currentUrl}`);
        results.push(result);
      } catch (error) {
        console.error(`[${role}] failed ${page.file}: ${error.message}`);
        results.push({ role, ...page, error: error.message });
      }
    }
  } finally {
    client.close();
    proc.kill();
  }
  return results;
}

(async () => {
  if (process.env.CAPTURE_ONLY === "customer-checkout") {
    const result = await captureGroup("customer", [customerPages.find((page) => page.file === "customer-06-payment-checkout")], 9332, "customer.mai@travelmate.test");
    const manifestPath = path.join(outDir, "customer_checkout_manifest.json");
    fs.writeFileSync(manifestPath, JSON.stringify(result, null, 2), "utf8");
    console.log(`manifest: ${manifestPath}`);
    return;
  }
  if (process.env.CAPTURE_ONLY === "owner-change-room") {
    const result = await captureGroup(
      "owner",
      [{ file: "owner-17-change-room", route: "/owner/bookings/18/change-room", title: "Đổi phòng", caption: "Giao diện đổi phòng cho booking đang lưu trú" }],
      9333,
      "owner.coast@travelmate.test"
    );
    const manifestPath = path.join(outDir, "owner_change_room_manifest.json");
    fs.writeFileSync(manifestPath, JSON.stringify(result, null, 2), "utf8");
    console.log(`manifest: ${manifestPath}`);
    return;
  }

  const manifest = [];
  manifest.push(...await captureGroup("public", publicPages, 9331));
  manifest.push(...await captureGroup("customer", customerPages, 9332, "customer.mai@travelmate.test"));
  manifest.push(...await captureGroup("owner", ownerPages, 9333, "owner.city@travelmate.test"));
  manifest.push(...await captureGroup("admin", adminPages, 9334, "admin@example.com"));
  const manifestPath = path.join(outDir, "screens_manifest.json");
  fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 2), "utf8");
  console.log(`manifest: ${manifestPath}`);
})().catch((error) => {
  console.error(error);
  process.exit(1);
});
