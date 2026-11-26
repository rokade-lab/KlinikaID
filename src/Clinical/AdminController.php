<?php
declare(strict_types=1);
namespace KlinikaID\Clinical;
use KlinikaID\Audit\Audit;
use KlinikaID\Http\{AppContext,Request,Response,ErrorResponder};
final class AdminController
{
    public function __construct(private readonly AppContext $app) {}
    public function index(Request $request): Response
    {
        if ($this->app->currentUser()?->role !== 'admin') return $this->denied($request);
        $repo=new AssignmentRepository($this->app->pdo());
        return Response::html($this->app->view()->page('admin/assignments','Assignment Dokter',[
            'assignments'=>$repo->all(),'doctors'=>$repo->doctors(),'visits'=>$repo->visits(),'activePath'=>'/admin/assignments',
        ],$this->app->currentUser()));
    }
    public function assign(Request $request): Response { return $this->change($request,false); }
    public function revoke(Request $request): Response { return $this->change($request,true); }
    private function denied(Request $request): Response
    {
        (new Audit($this->app))->record('assignment.manage','assignment',null,'deny');
        return (new ErrorResponder($this->app->view()))->respond($request,403,$this->app->currentUser());
    }
    private function change(Request $request,bool $revoke): Response
    {
        if ($this->app->currentUser()?->role !== 'admin') return $this->denied($request);
        $doctor=$request->postString('doctor_id'); $visit=$request->postString('visit_id');
        if (preg_match('/^[1-9][0-9]{0,17}$/D',$doctor)!==1 || preg_match('/^[1-9][0-9]{0,17}$/D',$visit)!==1) return $this->denied($request);
        $db=$this->app->pdo();
        $target=(new ProfileRepository($db))->find((int)$doctor);
        $repo=new AssignmentRepository($db);
        $exists=false;
        foreach ($repo->visits() as $row) if ((int)$row['id']===(int)$visit) { $exists=true; break; }
        if ($target===null || $target['role']!=='doctor' || !$target['active'] || !$exists) return $this->denied($request);
        $db->beginTransaction();
        try {
            if ($revoke) $repo->revoke((int)$doctor,(int)$visit); else $repo->assign((int)$doctor,(int)$visit);
            (new Audit($this->app))->record($revoke?'assignment.revoke':'assignment.create','visit',$visit,'allow');
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
        return Response::redirect('/admin/assignments',303);
    }
}
