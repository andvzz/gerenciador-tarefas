<?php

namespace App\Models;

use CodeIgniter\Model;

class TarefaModel extends Model
{
    protected $table            = 'tasks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['title', 'description', 'status'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'title'       => 'required|min_length[3]|max_length[255]',
        'description' => 'permit_empty|string',
        'status'      => 'required|in_list[pendente,em andamento,concluída]',
    ];

    protected $validationMessages = [
        'title' => [
            'required'   => 'Informe o título.',
            'min_length' => 'Mínimo de 3 caracteres.',
            'max_length' => 'Máximo de 255 caracteres.',
        ],
        'status' => [
            'required' => 'Selecione um status.',
            'in_list'  => 'Status inválido.',
        ],
    ];

    protected $skipValidation = false;
}
