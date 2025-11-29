<?php

namespace App\Controllers;

use App\Models\RecetaModel;

class ApiController
{
    private $recetaModel;

    public function __construct(RecetaModel $recetaModel)
    {
        $this->recetaModel = $recetaModel;
    }

}
