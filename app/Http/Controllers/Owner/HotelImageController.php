<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\HotelImage;
use App\Services\SystemLogService;
use Illuminate\Support\Facades\Storage;

class HotelImageController extends Controller
{
    public function destroy(HotelImage $hotelImage)
    {
        $hotelImage->load('hotel');

        if ((int) $hotelImage->hotel->owner_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền xóa ảnh này.');
        }

        Storage::disk('public')->delete($hotelImage->path);

        SystemLogService::write(
            'delete_hotel_gallery_image',
            'hotels',
            HotelImage::class,
            $hotelImage->id,
            'Owner xóa ảnh trong thư viện khách sạn.',
            [
                'hotel_id' => $hotelImage->hotel_id,
                'path' => $hotelImage->path,
            ]
        );

        $hotelImage->delete();

        return back()->with('success', 'Xóa ảnh thành công.');
    }
}