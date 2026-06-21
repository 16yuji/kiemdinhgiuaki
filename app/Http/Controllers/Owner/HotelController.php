<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\HotelImage;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    public function index()
    {
        $hotels = Hotel::with(['amenities', 'images'])
            ->where('owner_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('owner.hotels.index', compact('hotels'));
    }

    public function create()
    {
        $amenities = Amenity::where('status', 'active')
            ->where(function ($query) {
                $query->where('type', 'hotel')
                    ->orWhereNull('type');
            })
            ->orderBy('name')
            ->get();

        return view('owner.hotels.create', [
            'hotel' => new Hotel(),
            'amenities' => $amenities,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateHotel($request);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('hotels/thumbnails', 'public');
        }

        $data['owner_id'] = auth()->id();
        $data['status'] = 'active';

        $hotel = Hotel::create($data);

        $hotel->amenities()->sync($request->input('amenity_ids', []));

        $this->storeGalleryImages($request, $hotel);

        SystemLogService::write(
            'create_hotel',
            'hotels',
            Hotel::class,
            $hotel->id,
            'Owner tạo hồ sơ khách sạn.',
            [
                'hotel_name' => $hotel->name,
            ]
        );

        return redirect()
            ->route('owner.hotels.show', $hotel)
            ->with('success', 'Thêm khách sạn thành công.');
    }

    public function show(Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        $hotel->load([
            'amenities',
            'images',
            'roomTypes.rooms',
            'statusAppeals' => function ($query) {
                $query->latest();
            },
        ]);

        return view('owner.hotels.show', compact('hotel'));
    }

    public function edit(Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        $hotel->load(['amenities', 'images']);

        $amenities = Amenity::where('status', 'active')
            ->where(function ($query) {
                $query->where('type', 'hotel')
                    ->orWhereNull('type');
            })
            ->orderBy('name')
            ->get();

        return view('owner.hotels.edit', compact('hotel', 'amenities'));
    }

    public function update(Request $request, Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        $data = $this->validateHotel($request);

        if ($request->hasFile('thumbnail')) {
            if ($hotel->thumbnail) {
                Storage::disk('public')->delete($hotel->thumbnail);
            }

            $data['thumbnail'] = $request->file('thumbnail')->store('hotels/thumbnails', 'public');
        }

        $hotel->update($data);

        $hotel->amenities()->sync($request->input('amenity_ids', []));

        $this->storeGalleryImages($request, $hotel);

        SystemLogService::write(
            'update_hotel',
            'hotels',
            Hotel::class,
            $hotel->id,
            'Owner cập nhật hồ sơ khách sạn.',
            [
                'hotel_name' => $hotel->name,
            ]
        );

        return redirect()
            ->route('owner.hotels.show', $hotel)
            ->with('success', 'Cập nhật khách sạn thành công.');
    }

    public function destroy(Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        if ($hotel->bookings()->whereIn('status', ['pending_payment', 'confirmed', 'staying'])->exists()) {
            return back()->with('error', 'Không thể xóa khách sạn đang có đơn đặt phòng hiệu lực.');
        }

        foreach ($hotel->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        if ($hotel->thumbnail) {
            Storage::disk('public')->delete($hotel->thumbnail);
        }

        $hotel->amenities()->detach();
        $hotel->delete();

        return redirect()
            ->route('owner.hotels.index')
            ->with('success', 'Xóa khách sạn thành công.');
    }

    private function validateHotel(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'checkin_time' => ['nullable', 'date_format:H:i'],
            'checkout_time' => ['nullable', 'date_format:H:i'],
            'description' => ['nullable', 'string'],
            'cancellation_policy' => ['nullable', 'string', 'max:5000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['exists:amenities,id'],
        ], [
            'name.required' => 'Vui lòng nhập tên khách sạn.',
            'address.required' => 'Vui lòng nhập địa chỉ chi tiết.',
            'thumbnail.image' => 'Ảnh đại diện không hợp lệ.',
            'gallery_images.*.image' => 'Ảnh gallery không hợp lệ.',
        ]);
    }

    private function storeGalleryImages(Request $request, Hotel $hotel): void
    {
        if (!$request->hasFile('gallery_images')) {
            return;
        }

        $currentMaxSort = (int) $hotel->images()->max('sort_order');

        foreach ($request->file('gallery_images') as $index => $image) {
            HotelImage::create([
                'hotel_id' => $hotel->id,
                'path' => $image->store('hotels/gallery', 'public'),
                'caption' => null,
                'sort_order' => $currentMaxSort + $index + 1,
            ]);
        }
    }

    private function authorizeHotel(Hotel $hotel): void
    {
        if ((int) $hotel->owner_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền thao tác trên khách sạn này.');
        }
    }
}
