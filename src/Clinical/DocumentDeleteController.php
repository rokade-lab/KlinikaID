<?php
declare(strict_types=1);
namespace KlinikaID\Clinical;
use KlinikaID\Audit\Audit;
use KlinikaID\Http\{AppContext,Request,Response,ErrorResponder};
use KlinikaID\Storage\DocumentStorage;
use Throwable;
final class DocumentDeleteController
{
    public function __construct(private readonly AppContext $app) {}
    public function delete(Request $request): Response
    {
        $user = $this->app->currentUser();
        $errors = new ErrorResponder($this->app->view());
        if ($request->method !== 'GET') return $errors->respond($request,405,$user)->withHeader('Allow','GET');
        $id = $request->query['id'] ?? '';
        if (preg_match('/^[1-9][0-9]{0,17}$/D',$id) !== 1) return $errors->respond($request,404,$user);
        $db = $this->app->pdo();
        $storage = new DocumentStorage(getenv('STORAGE_ROOT') ?: '');
        $documents = new DocumentRepository($db);
        $deleted = false; $document = null; $bytes = null;
        $db->beginTransaction();
        try {
            $document = $documents->findForUpdate((int)$id);
            if ($document === null) {
                $db->rollBack();
                (new Audit($this->app))->record('document.delete','document',$id,'deny');
                return $errors->respond($request,404,$user);
            }
            $bytes = $storage->read($document['storage_key']);
            $visit = (new VisitRepository($db))->find((int)$document['visit_id']);

            $documents->delete((int)$id);
            (new Audit($this->app))->record('document.delete','document',$id,'allow',[
                'full_name'=>$visit['full_name'], 'medical_note_dummy'=>$visit['medical_note_dummy'],
            ]);
            $storage->delete($document['storage_key']);
            $deleted = true;
            $db->commit();
            return Response::redirect('/kunjungan?id=' . $document['visit_id'],303);
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            if ($deleted && $document !== null && is_string($bytes)) $storage->write($document['storage_key'],$bytes);
            if ($e instanceof \InvalidArgumentException || $e instanceof \RuntimeException) return $errors->respond($request,404,$user);
            throw $e;
        }
    }
}
