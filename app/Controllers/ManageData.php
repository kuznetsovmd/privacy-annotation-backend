<?php

namespace App\Controllers;

use App\Models\Selection;
use App\Models\Policy;
use Engine\Services\AuthService as Auth;
use Engine\Services\FileSystemService as FS;
use Engine\Request;
use Engine\View;
use Engine\Services\RedirectionService as Redirection;

/**
 * ManageData.php
 *
 * Controller class for loading annotation page.
 */
class ManageData
{

    /**
     * Goes to annotation page.
     *
     * @param Request $request
     */
    public static function toDataPage(Request $request)
    {
        $request->view = new View('data.php', [
            'title' => 'Data',
            'id' => Auth::authenticated(),
        ]);
    }

    /**
     * Uploads data for annotation.
     *
     * @param Request $request
     */
    public static function upload(Request $request)
    {
        Redirection::redirect('/home');

        $request->post_response = function () use ($request) {
            $descriptor = 'descriptor.jsonl';
            $documents = 'markdown';
            $key = 'hash';
            $tmp_file = $request->parameters['files']['data']['tmp_name'];
            $hash = md5(md5(rand()));

            $archive = "$hash.tar.gz";
            FS::resource($tmp_file, $archive);
            FS::untar($archive, $hash);

            $descriptor_file = FS::resolve("$hash/$descriptor");
            $portion = 100;
            $policies = [];

            $handle = fopen($descriptor_file, 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $row = json_decode($line, true);
                    if (!$row) continue;

                    $content = FS::read("$hash/$documents/{$row[$key]}.md");
                    $policies[$row[$key]] = str_replace("\r", '', $content);

                    if (--$portion < 1) {
                        Policy::create($policies);
                        $portion = 100;
                        $policies = [];
                    }
                }
                fclose($handle);
            }

            if (!empty($policies)) {
                Policy::create($policies);
            }

            FS::rmdir($hash, true);

        };
    }

    /**
     * Gives annotation data for download.
     *
     * @param Request $request
     */
    public static function download(Request $request)
    {
        $hash = md5(rand());
        $file = "$hash.jsonl";

        $data = Selection::packWithUsers();

        $lines = [];
        foreach ($data as $item) {
            $lines[] = json_encode($item, JSON_UNESCAPED_UNICODE);
        }

        FS::write($file, implode("\n", $lines));
        FS::download($file, true);
    }

}
