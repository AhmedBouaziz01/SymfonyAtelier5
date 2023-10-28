<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class StudentController extends AbstractController
{
    public function affiche()
    {
        return new Response(content: 'Bonjour mes étudiants');
    }
}
