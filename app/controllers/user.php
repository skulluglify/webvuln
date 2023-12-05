<?php
namespace App\Controllers;

use App\Models\SimpleTodosDatabaseMySQL;
use Skfw\Cabbage\HttpResponse;
use Skfw\Enums\HttpMethod;
use Skfw\Enums\HttpStatusCode;
use Skfw\Interfaces\Cabbage\IHttpRequest;
use Skfw\Interfaces\Cabbage\IHttpResponse;
use Skfw\Interfaces\Cabbage\IMiddleware;
use Skfw\Tags\Route;

class UserController
{
    private SimpleTodosDatabaseMySQL $simple;

    public function __construct()
    {
        $this->simple = new SimpleTodosDatabaseMySQL();
    }

    private function _todos_collate(array $todo): array
    {
        $timestamp = time_unix();

        $todo['status'] = 'on progress';  // on progress, finish, due to
        $todo['finished'] = false;

        $created_at = $todo['created_at'];
        $deadline = $todo['deadline'];
        $finished_at = $todo['finished_at'];

        $created_at = !empty($created_at) ? datetime_to_timestamp($created_at) : null;
        $deadline = !empty($deadline) ? datetime_to_timestamp($deadline) : null;
        $finished_at = !empty($finished_at) ? datetime_to_timestamp($finished_at) : null;

        $todo['created_at'] = $created_at;
        $todo['deadline'] = $deadline;
        $todo['finished_at'] = $finished_at;

        if (!empty($created_at) and !empty($deadline))
        {
            if ($deadline < $timestamp) $todo['status'] = 'due date';

            if (!empty($finished_at))
            {
                $todo['status'] = 'finish';
                $todo['finished'] = true;
            }

        }

        return $todo;
    }

    #[Route('/todos')]
    public function todos(IHttpRequest $request): ?IHttpResponse
    {
        if ($request->method() === HttpMethod::GET)
        {
            $data = $this->simple->get_todos();
            $data = array_map(fn(array $v): array => $this->_todos_collate($v), $data);
            $temp = json_encode($data);
            return new HttpResponse($temp, headers: headers([
                'content-type' => 'application/json',
            ]));
        }
    }

    #[Route('/init')]
    public function init_db(IHttpRequest $request): ?IHttpResponse
    {
        $this->simple->init();
        $this->simple->add_user('admin', 'admin@mail.co', '1234');
        return new HttpResponse('table has been created! user: admin, pass: 1234');
    }

    #[Route('/login')]
    public function login(IHttpRequest $request): ?IHttpResponse
    {
        if ($request->method() === HttpMethod::POST)
        {
            $body = $request->body();

            if (!empty($body))
            {
                $email = (string)$body["email"];
                $password = (string)$body["password"];

                $data = $this->simple->get_user(email: $email);
                if (!empty($data))
                {
                    if (str_comp_case($password, $data["password"], case: 0))
                        return new HttpResponse('{"done":true,"status":"success","message":"success login!","data":{"token":"fakeToken"}}',
                            headers: headers(['content-type' => 'application/json']));
                }
                return new HttpResponse('{"done":false,"status":"failure","message":"failed login!"}', HttpStatusCode::UNAUTHORIZED,
                    headers: headers(['content-type' => 'application/json']));
            }

            return new HttpResponse("data is not valid!", HttpStatusCode::BAD_REQUEST);
        }
    }
}