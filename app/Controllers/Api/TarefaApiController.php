<?php

namespace App\Controllers\Api;

use App\Models\TarefaModel;
use CodeIgniter\RESTful\ResourceController;

class TarefaApiController extends ResourceController
{
    protected $modelName = TarefaModel::class;
    protected $format    = 'json';

    public function index()
    {
        return $this->respond($this->model->orderBy('created_at', 'DESC')->findAll());
    }

    public function show($id = null)
    {
        $tarefa = $this->model->find($id);

        if ($tarefa === null) {
            return $this->failNotFound("Tarefa não encontrada com o ID {$id}.");
        }

        return $this->respond($tarefa);
    }

    public function create()
    {
        $data = $this->request->getJSON(true) ?: $this->request->getPost();

        if (! $this->model->insert($data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        $data['id'] = $this->model->getInsertID();

        return $this->respondCreated($data, 'Tarefa criada com sucesso.');
    }

    public function update($id = null)
    {
        $tarefa = $this->model->find($id);

        if ($tarefa === null) {
            return $this->failNotFound("Tarefa não encontrada com o ID {$id}.");
        }

        $data = $this->request->getJSON(true) ?: $this->request->getRawInput();

        if (! $this->model->update($id, $data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        return $this->respond($this->model->find($id), 200, 'Tarefa atualizada com sucesso.');
    }

    public function delete($id = null)
    {
        $tarefa = $this->model->find($id);

        if ($tarefa === null) {
            return $this->failNotFound("Tarefa não encontrada com o ID {$id}.");
        }

        $this->model->delete($id);

        return $this->respondDeleted(['id' => $id], 'Tarefa excluída com sucesso.');
    }
}
