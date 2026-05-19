<?php

namespace App\Services;

use Exception;
use ModbusTcpClient\Exception\ModbusException;
use ModbusTcpClient\Network\BinaryStreamConnection;
use ModbusTcpClient\Packet\ResponseFactory;
use ModbusTcpClient\Packet\ModbusFunction\ReadHoldingRegistersRequest;
use ModbusTcpClient\Packet\ModbusFunction\ReadHoldingRegistersResponse;


class ModbusService
{
    public function readHoldingRegistersFromDevice(string $ip, int $slaveId, int $startAddress, int $quantity = 1)
    {
        try {
            $connection = BinaryStreamConnection::getBuilder()
                ->setHost($ip)
                ->setPort(502)
                ->setConnectTimeoutSec(5)
                ->build();

            $packet = new ReadHoldingRegistersRequest(
                $startAddress,
                $quantity,
                $slaveId
            );

            $binaryData = $connection
                ->connect()
                ->sendAndReceive($packet);

            $response = ResponseFactory::parseResponseOrThrow($binaryData);

            if (!($response instanceof ReadHoldingRegistersResponse)) {
                throw new Exception('Unexpected response type');
            }

            return [
                'error' => false,
                'data'  => $response->getData()
            ];

        } catch (Exception $e) {
            return [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }
    }
}