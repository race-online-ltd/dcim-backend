<?php

namespace App\Http\Controllers;

use App\Services\ModbusService;

class ModbusController extends Controller
{
    public function index(ModbusService $modbus)
    {
        $data = $modbus->readHoldingRegisters(97, 3);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}