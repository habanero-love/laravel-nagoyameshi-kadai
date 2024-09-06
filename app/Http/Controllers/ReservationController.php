<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Http\Requests\ReservationRequest;
use App\Models\Restaurant;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::where('user_id', auth()->id())
            ->orderBy('reserved_datetime', 'desc')
            ->paginate(15);

        return view("reservations.index", compact('reservations'));
    }

    public function create(Restaurant $restaurant)
    {
        return view("reservations.create", compact('restaurant'));
    }

    public function store(ReservationRequest $request,Restaurant $restaurant)
    {
        Reservation::create(array_merge(
            $request->validated(),
            [
                'reserved_datetime' => $request->input('reservation_date') . ' ' . $request->input('reservation_time'),
                'user_id' => auth()->id(),
                'restaurant_id' => $restaurant->id,
            ]
        ));

        return redirect()->route('reservations.index')->with('flash_message', '予約が完了しました。');
    }

    public function destroy(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            return redirect()->route('reservations.index')->with('error_message', '不正なアクセスです。');
        };

        // データベースの削除
        $reservation->delete();

        return redirect()->route('reservations.index')->with('flash_message', '予約をキャンセルしました。');
    }
}
