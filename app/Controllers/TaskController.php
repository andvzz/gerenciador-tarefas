<?php

namespace App\Controllers;

use App\Models\TaskModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class TaskController extends BaseController
{
    protected TaskModel $taskModel;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
    }

    public function index()
    {
        $data = [
            'title'   => 'Minhas Tarefas',
            'tarefas' => $this->taskModel->orderBy('created_at', 'DESC')->findAll(),
        ];

        return view('tasks/index', $data);
    }

    public function store()
    {
        $data = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'status'      => $this->request->getPost('status'),
        ];

        if (! $this->taskModel->save($data)) {
            return $this->respondError($this->taskModel->errors());
        }

        $task = $this->taskModel->find($this->taskModel->getInsertID());

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Tarefa criada com sucesso!',
                'task'    => $task,
                'csrf'    => csrf_hash(),
            ]);
        }

        return redirect()->to('/tasks')->with('message', 'Tarefa criada com sucesso!');
    }

    public function update($id = null)
    {
        $task = $this->taskModel->find($id);

        if ($task === null) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)
                    ->setJSON(['status' => 'error', 'message' => 'Tarefa não encontrada.', 'csrf' => csrf_hash()]);
            }

            throw PageNotFoundException::forPageNotFound('Tarefa não encontrada.');
        }

        $data = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'status'      => $this->request->getPost('status'),
        ];

        if (! $this->taskModel->update($id, $data)) {
            return $this->respondError($this->taskModel->errors());
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Tarefa atualizada com sucesso!',
                'task'    => $this->taskModel->find($id),
                'csrf'    => csrf_hash(),
            ]);
        }

        return redirect()->to('/tasks')->with('message', 'Tarefa atualizada com sucesso!');
    }

    private function respondError(array $errors)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'errors' => $errors,
                'csrf'   => csrf_hash(),
            ]);
        }

        return redirect()->back()
            ->withInput()
            ->with('errors', $errors);
    }

    public function delete($id = null)
    {
        $task = $this->taskModel->find($id);

        if ($task === null) {
            throw PageNotFoundException::forPageNotFound('Tarefa não encontrada.');
        }

        $this->taskModel->delete($id);

        return redirect()->to('/tasks')->with('message', 'Tarefa excluída com sucesso!');
    }

    public function updateStatus()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['error' => 'Requisição inválida.']);
        }

        $id         = $this->request->getPost('id');
        $status     = $this->request->getPost('status');
        $permitidos = ['pendente', 'em andamento', 'concluída'];

        if (empty($id) || ! in_array($status, $permitidos, true)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'Dados inválidos.', 'csrf' => csrf_hash()]);
        }

        if ($this->taskModel->find($id) === null) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'Tarefa não encontrada.', 'csrf' => csrf_hash()]);
        }

        $salvo = $this->taskModel->update($id, ['status' => $status]);

        return $this->response->setJSON([
            'success' => (bool) $salvo,
            'csrf'    => csrf_hash(),
        ]);
    }
}
