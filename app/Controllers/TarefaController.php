<?php

namespace App\Controllers;

use App\Models\TarefaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class TarefaController extends BaseController
{
    protected TarefaModel $tarefaModel;

    public function __construct()
    {
        $this->tarefaModel = new TarefaModel();
    }

    public function index()
    {
        $data = [
            'title'   => 'Minhas Tarefas',
            'tarefas' => $this->tarefaModel->orderBy('created_at', 'DESC')->findAll(),
        ];

        return view('tarefas/index', $data);
    }

    public function salvar()
    {
        $data = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'status'      => $this->request->getPost('status'),
        ];

        if (! $this->tarefaModel->save($data)) {
            return $this->responderErro($this->tarefaModel->errors());
        }

        $tarefa = $this->tarefaModel->find($this->tarefaModel->getInsertID());

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Tarefa criada com sucesso!',
                'tarefa'  => $tarefa,
                'csrf'    => csrf_hash(),
            ]);
        }

        return redirect()->to('/tarefas')->with('message', 'Tarefa criada com sucesso!');
    }

    public function atualizar($id = null)
    {
        $tarefa = $this->tarefaModel->find($id);

        if ($tarefa === null) {
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

        if (! $this->tarefaModel->update($id, $data)) {
            return $this->responderErro($this->tarefaModel->errors());
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Tarefa atualizada com sucesso!',
                'tarefa'  => $this->tarefaModel->find($id),
                'csrf'    => csrf_hash(),
            ]);
        }

        return redirect()->to('/tarefas')->with('message', 'Tarefa atualizada com sucesso!');
    }

    private function responderErro(array $errors)
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

    public function excluir($id = null)
    {
        $tarefa = $this->tarefaModel->find($id);

        if ($tarefa === null) {
            throw PageNotFoundException::forPageNotFound('Tarefa não encontrada.');
        }

        $this->tarefaModel->delete($id);

        return redirect()->to('/tarefas')->with('message', 'Tarefa excluída com sucesso!');
    }

    public function atualizarStatus()
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

        if ($this->tarefaModel->find($id) === null) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'Tarefa não encontrada.', 'csrf' => csrf_hash()]);
        }

        $salvo = $this->tarefaModel->update($id, ['status' => $status]);

        return $this->response->setJSON([
            'success' => (bool) $salvo,
            'csrf'    => csrf_hash(),
        ]);
    }
}
