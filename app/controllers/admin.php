<?php
namespace App\Controllers;

use App\Middlewares\ExampleMiddleware;
use App\Middlewares\FakeAuthMiddleware;
use Exception;
use App\Models\SimpleTodosDatabaseMySQL;
use Skfw\Cabbage\HttpResponse;
use Skfw\Enums\HttpMethod;
use Skfw\Enums\HttpStatusCode;
use Skfw\Interfaces\Cabbage\IHttpRequest;
use Skfw\Interfaces\Cabbage\IHttpResponse;
use Skfw\Interfaces\Cabbage\IMiddleware;
use Skfw\Tags\PathTag;
use Skfw\Tags\Route;

class AdminController {
    private SimpleTodosDatabaseMySQL $simple;

    public function __construct()
    {
        $this->simple = new SimpleTodosDatabaseMySQL();
    }

    public function prefix(): string
    {
        return '/admin';
    }

    /**
     * @return IMiddleware[]
     * @throws Exception
     */
    public function middlewares(): array
    {
        return [
            new FakeAuthMiddleware(),
        ];
    }
    #[Route('/todos/add')]
    public function add_todo(IHttpRequest $request): ?IHttpResponse
    {
        if ($request->method() === HttpMethod::POST)
        {
            $body = $request->body();

            if (!empty($body)) {

                $description = $body['description'];
                $deadline = $body['deadline'];

                if ($this->simple->add_todo($description, $deadline)) return new HttpResponse('{"done": true,"status": "success","message":"successful add todo!"}',
                    headers: headers(['content-type' => 'application/json']));
                else return new HttpResponse('{"done": false,"status": "failure","message":"failed add todo!"}',
                    headers: headers(['content-type' => 'application/json']));
            }

            return new HttpResponse("data is not valid!", HttpStatusCode::BAD_REQUEST);
        }

        return null;
    }
    #[Route('/todos/edit')]
    public function edit_todo(IHttpRequest $request): ?IHttpResponse
    {
        if ($request->method() === HttpMethod::PUT)
        {
            $id = $request->param('id')?->shift();
            $body = $request->body();
            if (!empty($id) && !empty($body)) {

                $id = (int)$id;
                $description = (string)$body['description'];
                $deadline = (int)$body['deadline'];
                $finished = (bool)$body['finished'];

                if ($this->simple->edit_todo($id, $description, $deadline, $finished)) return new HttpResponse('{"done": true,"status": "success","message":"successful edit todo!"}',
                    headers: headers(['content-type' => 'application/json']));
                else return new HttpResponse('{"done": false,"status": "failure","message":"failed edit todo!"}',
                    headers: headers(['content-type' => 'application/json']));
            }

            return new HttpResponse("data is not valid!", HttpStatusCode::BAD_REQUEST);
        }

        return null;
    }

    #[Route('/todos/del')]
    public function del_todo(IHttpRequest $request): ?IHttpResponse
    {
        if ($request->method() === HttpMethod::DELETE)
        {
            $id = $request->param('id')?->shift();
            if (!empty($id)) {

                $id = (int)$id;

                if ($this->simple->del_todo($id)) return new HttpResponse('{"done": true,"status": "success","message":"successful delete todo!"}',
                    headers: headers(['content-type' => 'application/json']));
                else return new HttpResponse('{"done": false,"status": "failure","message":"failed delete todo!"}',
                    headers: headers(['content-type' => 'application/json']));
            }

            return new HttpResponse("data is not valid!", HttpStatusCode::BAD_REQUEST);
        }

        return null;
    }
}
