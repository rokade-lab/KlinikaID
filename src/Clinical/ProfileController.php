<?php

declare(strict_types=1);

namespace KlinikaID\Clinical;

use KlinikaID\Audit\Audit;
use KlinikaID\Http\AppContext;
use KlinikaID\Http\ErrorResponder;
use KlinikaID\Http\Request;
use KlinikaID\Http\Response;

final class ProfileController
{
    public function __construct(private readonly AppContext $app) {}

    public function edit(Request $request): Response
    {
        $user = $this->app->currentUser();

        $target = $request->method === 'POST'
            ? ($request->post['user_id'] ?? $request->query['user_id'] ?? $user?->id)
            : ($request->query['user_id'] ?? $user?->id);
        if ($target === null) {
            return Response::redirect('/login');
        }
        if ((!is_string($target) && !is_int($target)) || preg_match('/^[1-9][0-9]{0,17}$/D', (string) $target) !== 1) {
            return (new ErrorResponder($this->app->view()))->respond($request, 404, $user);
        }
        $repository = new ProfileRepository($this->app->pdo());
        $profile = $repository->find((int) $target);
        if ($profile === null) {
            return (new ErrorResponder($this->app->view()))->respond($request, 404, $user);
        }
        if ($request->method === 'POST') {
            $name = trim($request->postString('display_name'));
            if ($name === '' || strlen($name) > 200) {
                return (new ErrorResponder($this->app->view()))->respond($request, 403, $user);
            }
            $fields = ['display_name' => $name];

            $role = array_key_exists('role', $request->post) ? $request->postString('role') : $profile['role'];
            $patient = array_key_exists('patient_id', $request->post) ? $request->postString('patient_id') : $profile['patient_id'];
            if ($patient === '') $patient = null;
            if (!in_array($role, ['patient', 'doctor', 'frontdesk', 'admin'], true)
                || (($role === 'patient') !== ($patient !== null))
                || ($patient !== null && preg_match('/^[1-9][0-9]{0,17}$/D', (string) $patient) !== 1)) {
                return (new ErrorResponder($this->app->view()))->respond($request, 403, $user);
            }
            if ($patient !== null) {
                $q = $this->app->pdo()->prepare('SELECT 1 FROM patients WHERE id = :id');
                $q->execute(['id' => (int) $patient]);
                if ($q->fetchColumn() === false) return (new ErrorResponder($this->app->view()))->respond($request, 403, $user);
            }
            if (array_key_exists('role', $request->post)) $fields['role'] = $role;
            if (array_key_exists('patient_id', $request->post)) $fields['patient_id'] = $patient;
            $db = $this->app->pdo();
            $db->beginTransaction();
            try {
                $repository->update((int) $profile['id'], $fields);
                (new Audit($this->app))->record('profile.update', 'user', $profile['id'], 'allow');
                $db->commit();
            } catch (\Throwable $e) {
                if ($db->inTransaction()) $db->rollBack();
                throw $e;
            }
            return Response::redirect('/profil/ubah?user_id=' . $profile['id'], 303);
        }
        return Response::html($this->app->view()->page('profil/edit', 'Ubah profil', [
            'profile' => $profile,
            'activePath' => '/profil/ubah',
        ], $user));
    }
}
