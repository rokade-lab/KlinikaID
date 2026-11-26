<?php

declare(strict_types=1);

namespace KlinikaID\Clinical;

use KlinikaID\Http\AppContext;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;

final class DocumentDownloadController
{
    public function __construct(private readonly AppContext $app) {}

    public function download(Request $request): Response
    {
        $id = filter_var($request->query['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $document = $id === false ? null : (new DocumentRepository($this->app->pdo()))->find($id);

        return (new DocumentViewResponder($this->app))->respond($request, $document, true);
    }
}
