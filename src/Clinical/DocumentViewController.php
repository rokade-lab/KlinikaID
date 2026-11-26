<?php

declare(strict_types=1);

namespace KlinikaID\Clinical;

use KlinikaID\Http\AppContext;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;
use KlinikaID\Storage\DocumentStorage;

final class DocumentViewController
{
    public function __construct(private readonly AppContext $app) {}

    public function show(Request $request): Response
    {
        $id = filter_var($request->query['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $repository = new DocumentRepository($this->app->pdo());
        if (array_key_exists('file', $request->query)) {
            $key = $request->query['file'];
            $document = DocumentStorage::isValidKey($key) ? $repository->findByStorageKey($key) : null;
        } else {
            $document = $id === false ? null : $repository->find($id);
        }

        return (new DocumentViewResponder($this->app))->respond($request, $document);
    }
}
