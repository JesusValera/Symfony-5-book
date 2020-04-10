<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ConferenceController extends AbstractController
{
    /**
     * @Route("/", name="conference")
     */
    public function index(Request $request): Response
    {
        $greet = '';
        if ($name = $request->query->get('hello')) {
            $greet = sprintf('<h1>Hello %s!</h1>', htmlspecialchars($name));
        }

        return new Response(<<<EOF
<HTML>
  <body>
    <img src="/images/under-construction.gif">
    $greet
  </body>
</HTML>
EOF
        );
    }
}
