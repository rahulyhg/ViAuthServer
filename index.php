<?php
//  Copyright (c) 2017, Patrick Minogue. All rights reserved. Use of this source code
//  is governed by a BSD-style license that can be found in the LICENSE file.

const AUTH_BASE = "";
const HTTP_ORIGIN = "";

header('Accept-Charset: UTF-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Content-Type: application/json');

/// REMOVE IN PRODUCTION ///
header("Access-Control-Allow-Origin: " . HTTP_ORIGIN);
ini_set('display_errors', 1);
error_reporting(E_ALL);
////////////////////////////

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST')
{
    // Retrieve the table and key from the path, and input from php://input
    $request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
    $command = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));

    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['client'])) respond(400, "Client not specified");

    require_once(__DIR__ . AUTH_BASE);
    $auth = new ViAuth($input['client']);

    $response = null;

    switch ($command)
    {
        case "fetch_last_login":
            validOrDie($input, ["Username"]);
            $response = $auth->fetchLastLogin($input['username']);
            break;

        case "login_with_token":
            validOrDie($input, ["Token"]);
            $response = $auth->loginWithToken($input['token']);
            break;

        case "login_with_username_password":
            validOrDie($input, ["Username", "Password"]);
            $response = $auth->loginWithUsernamePassword($input['username'], $input['password']);
            break;

        case "register":
            validOrDie($input, ["Username"]);
            $response = $auth->register($input['username'], isset($input['password']) ? $input['password'] : null);
            break;

        case "reset_password":
            validOrDie($input, ["Username", "Password", "Token"]);
            $response = $auth->resetPassword($input['username'], $input['password'], $input['token']);
            break;

        case "reset_token":
            validOrDie($input, ["Username"]);
            $response = $auth->resetToken($input['username']);
            break;

        case "unregister":
            validOrDie($input, ["Username"]);
            $response = $auth->unregister($input['username']);
            break;

        case "validate_token":
            validOrDie($input, ["Token"]);
            $response = $auth->validateToken($input['token']);
            break;

        default:
            respond(404, "Command '$command' not found");
            break;
    }

    respond(http_response_code(), $response);
}

function validOrDie($input, $keys)
{
    foreach ($keys as $key)
    {
        if (empty($input[strtolower($key)])) respond(401, "$key not specified");
    }
}

function respond($status, $body)
{
    http_response_code($status);
    die($body);
}
