<?php

use Smadi0x86wsl\Backend\Controller\UserController;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use Smadi0x86wsl\Backend\Database\DatabaseConnection;
use Monolog\Logger;

beforeEach(function () {
    $this->dbConnection = Mockery::mock(DatabaseConnection::class);
    $this->logger = Mockery::mock(Logger::class);
    $this->server = Mockery::mock(Server::class);

    $this->logger->shouldReceive('error')->withAnyArgs()->zeroOrMoreTimes();
    $this->logger->shouldReceive('info')->zeroOrMoreTimes();

    $this->controller = new UserController($this->server, $this->logger);
});

test('it registers a user successfully', function () {
    $uniqueUser = 'testuser_' . bin2hex(random_bytes(1));
    $uniqueEmail = 'test@example.com';

    // Set up request mock
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('getContent')->andReturn(json_encode([
        'username' => $uniqueUser,
        'password' => 'testpass',
        'email' => $uniqueEmail
    ]));

    // Set up response mock
    $response = Mockery::mock(Response::class);
    $response->shouldReceive('header')->with('Content-Type', 'application/json')->once();
    $response->shouldReceive('status')->withArgs(function ($code) {
        return in_array($code, [200, 409, 500]);
    })->atLeast()->once();
    $response->shouldReceive('end')->atLeast()->once();

    // Logger expectations
    $this->logger->shouldReceive('error')->zeroOrMoreTimes();
    $this->logger->shouldReceive('info')->zeroOrMoreTimes();

    // Execute registration
    $this->controller->register($request, $response);
});


