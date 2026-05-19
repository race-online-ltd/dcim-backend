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
    protected $connection;

    public function __construct()
    {
        $this->connection = BinaryStreamConnection::getBuilder()
            ->setHost('172.16.3.12')
            ->setPort(502)
            ->setConnectTimeoutSec(5)
            ->build();
    }

    public function readHoldingRegisters($startAddress = 0, $quantity = 1)
    {
        try {

            $packet = new ReadHoldingRegistersRequest(
                $startAddress,
                $quantity,
                1
            );

            $binaryData = $this->connection
                ->connect()
                ->sendAndReceive($packet);

            $response = ResponseFactory::parseResponseOrThrow($binaryData);

            if (!($response instanceof ReadHoldingRegistersResponse)) {
                throw new Exception('Unexpected response type');
            }

            return $response->getData();

        } catch (Exception $e) {

            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}