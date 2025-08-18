<?php

namespace App\Controllers;

use App\Models\Policy;
use App\Models\Selection;
use Engine\Services\RedirectionService as Redirection;
use Engine\Services\SessionService as Session;
use Engine\Services\AuthService as Auth;
use Engine\Request;
use Engine\View;

/**
 * Annotation.php
 *
 * Controller class for loading annotation page.
 */
class Annotation
{

    /**
     * Goes to annotation page.
     *
     * @param Request $request
     */
    public static function toAnnotationPage(Request $request)
    {   
        $hash = Session::get('hash');

        $policy = null;
        if (isset($hash)) {
            $policy = Policy::getExact($hash);
        } else {
            $policy = Policy::getRandom();
            if (!empty($policy)) Session::set('hash', $policy['hash']);
        }

        $request->view = new View('annotation.php', [
            'title' => 'Annotation',
            'id' => Auth::authenticated(),
            'policy' => $policy,
        ]);
    }

    /**
     * Process the annotations.
     *
     * @param Request $request
     */
    public static function processAnnotations(Request $request)
    {   
        Redirection::redirect('/home');

        $id = Auth::authenticated();
        $hash = Session::get('hash');
        $policy = Policy::getExact($hash)['content'];

        Session::set('hash', null);

        $request->post_response = function () use ($request, $id, $hash, $policy) {
            $annotations = json_decode($request->parameters['json']);

            $portion = 100;
            $rows = [];
            foreach ($annotations as $annotation) {

                $rows[] = [
                    'starts_on'         => $annotation->selection->sc,
                    'ends_on'           => $annotation->selection->ec,
                    'selection_class'   => $annotation->metaLayer->class,
                    'selection_content' => mb_substr(
                        $policy, 
                        $annotation->selection->sc, 
                        $annotation->selection->ec - $annotation->selection->sc
                    ),
                    'user_id'           => $id,
                    'hash'              => $hash,
                ];

                if ($portion-- < 1) {
                    Selection::create($rows);
                    $portion = 100;
                    $rows = [];
                }
            }
            Selection::create($rows);

        };

    }

}
