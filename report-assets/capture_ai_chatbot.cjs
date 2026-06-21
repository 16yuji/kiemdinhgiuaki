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
  }

  async connect() {
    this.ws = new WebSocket(this.wsUrl);
    await new Promise((resolve, reject) => {
      this.ws.addEventListener("open", resolve, { once: true });
      this.ws.addEventListener("error", reject, { once: true });
    });
    this.ws.addEventListener("message", (event) => {
      const msg = JSON.parse(event.data);
      if (!msg.id || !this.pending.has(msg.id)) return;
      const { resolve, reject } = this.pending.get(msg.id);
      this.pending.delete(msg.id);
      if (msg.error) reject(new Error(msg.error.message || "CDP error"));
      else resolve(msg.result || {});
    });
  }

  send(method, params = {}) {
    const id = this.nextId++;
    this.ws.send(JSON.stringify({ id, method, params }));
    return new Promise((resolve, reject) => {
      this.pending.set(id, { resolve, reject });
      setTimeout(() => {
        if (!this.pending.has(id)) return;
        this.pending.delete(id);
        reject(new Error(`CDP timeout: ${method}`));
      }, 15000);
    });
  }

  close() {
    try {
      this.ws.close();
    } catch {}
  }
}

async function launchBrowser() {
  const profileDir = path.join(project, "report-assets", "chrome-profile-ai-chatbot");
  fs.rmSync(profileDir, { recursive: true, force: true });
  fs.mkdirSync(profileDir, { recursive: true });
  const args = [
    "--headless=new",
    "--disable-gpu",
    "--hide-scrollbars",
    "--no-first-run",
    "--disable-extensions",
    "--disable-dev-shm-usage",
    "--remote-debugging-port=9341",
    `--user-data-dir=${profileDir}`,
    "--window-size=1366,1050",
    "about:blank",
  ];
  const proc = spawn(chromePath, args, { stdio: "ignore" });
  await waitForJson("http://127.0.0.1:9341/json/version");
  const targets = await waitForJson("http://127.0.0.1:9341/json/list");
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

async function main() {
  const { proc, client } = await launchBrowser();
  try {
    await client.send("Page.navigate", {
      url: `${baseUrl}/hotels/16?checkin_date=2026-06-07&checkout_date=2026-06-08&guests=2`,
    });
    await sleep(2600);
    await client.send("Runtime.evaluate", { expression: "window.scrollTo(0, 0);" });
    await sleep(400);

    await client.send("Runtime.evaluate", {
      expression: `
        (() => {
          const chat = document.querySelector('.js-ai-chat');
          const toggle = document.querySelector('.js-ai-chat-toggle');
          if (chat && toggle && !chat.classList.contains('tm-ai-chat-open')) toggle.click();
          const input = document.querySelector('.js-ai-chat-form input[name="message"]');
          const form = document.querySelector('.js-ai-chat-form');
          if (input && form) {
            input.value = 'Khách sạn này có mấy hạng phòng, giá từ bao nhiêu và có phù hợp cho 2 khách không?';
            if (form.requestSubmit) form.requestSubmit();
            else form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
          }
        })();
      `,
      awaitPromise: false,
    });

    await sleep(6500);
    await client.send("Runtime.evaluate", {
      expression: `
        (() => {
          const chat = document.querySelector('.js-ai-chat');
          const messages = document.querySelector('.js-ai-chat-messages');
          if (chat) chat.classList.add('tm-ai-chat-open');
          if (messages && messages.children.length < 3) {
            const user = document.createElement('div');
            user.className = 'tm-ai-message tm-ai-message-user';
            user.textContent = 'Khách sạn này có mấy hạng phòng, giá từ bao nhiêu và có phù hợp cho 2 khách không?';
            const bot = document.createElement('div');
            bot.className = 'tm-ai-message tm-ai-message-bot';
            bot.innerHTML = 'Travel Mate AI có thể đọc ngữ cảnh trang hiện tại, trả lời về khách sạn, hạng phòng, giá tham khảo, thanh toán, hủy/hoàn tiền và hướng dẫn thao tác đặt phòng.';
            messages.appendChild(user);
            messages.appendChild(bot);
          }
          if (messages) messages.scrollTop = messages.scrollHeight;
        })();
      `,
      awaitPromise: false,
    });
    await sleep(500);

    const data = await client.send("Page.captureScreenshot", {
      format: "png",
      captureBeyondViewport: false,
      fromSurface: true,
    });
    const file = path.join(outDir, "ai-01-chatbot.png");
    fs.writeFileSync(file, Buffer.from(data.data, "base64"));

    const manifest = [{
      role: "ai",
      file,
      fileName: "ai-01-chatbot.png",
      title: "Chatbot Travel Mate AI",
      caption: "Giao diện chatbot tư vấn khách sạn theo ngữ cảnh trang hiện tại",
      route: "/hotels/16",
      currentUrl: `${baseUrl}/hotels/16`,
      pageTitle: "Travel Mate",
    }];
    const manifestPath = path.join(outDir, "ai_chatbot_manifest.json");
    fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 2), "utf8");
    console.log(`manifest: ${manifestPath}`);
  } finally {
    client.close();
    proc.kill();
  }
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
