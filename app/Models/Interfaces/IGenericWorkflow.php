<?php

namespace App\Models\Interfaces;

interface IGenericWorkflow {
    public function getCurrentState();
}