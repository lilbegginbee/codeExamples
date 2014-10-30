<?php
/**
 * Добавляет аватар пользователям
 */

namespace App\Commands\Misc;

use App\Commands\zendLoadTrait;
use App\Commands\AppCommand;
use emberlabs\GravatarLib\Gravatar;
use Model_User;
use AG_Service_Users;

class DefaultAvatars extends AppCommand
{
    use zendLoadTrait;

    protected $defaultPath = 'public/images/resize/original/';
    protected $path = null;

    public function __construct()
    {
        parent::__construct();
        $this->initZend();
        $this->path = APPLICATION_PATH . '/../' . $this->defaultPath . '/';
    }

    protected function configure()
    {
        $this->setName("misc:avatars")
            ->setDescription("Set avatars for all non-avatars users")
            ->setHelp("The <info>misc:avatars</info> command runs without any arguments.");
    }

    public function doExecute()
    {

        $sUsers = new AG_Service_Users();
        $mUser = new Model_User();

        $users = $sUsers->getUsers([
            ['avatar', 'is', null]
        ]);

        $gravatar = new Gravatar();
        $gravatar->setDefaultImage('wavatar')
            ->setAvatarSize(300)
            ->setMaxRating('x');

        $getAvaPath = function ($id, $ext = '.png', $depth = 3) {
            $hash = md5($id);
            $path = '';
            while ($depth--) {
                $path .= $hash[$depth] . '/';
            }
            $path .= $hash . $ext;
            return $path;
        };

        foreach ($users as $user) {
            $avatar = $gravatar->buildGravatarURL($user['id']);
            $avaImage = file_get_contents(html_entity_decode($avatar));
            $avaImageName = $getAvaPath($user['id']);
            $path = $this->path . $avaImageName;
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }
            file_put_contents($path, $avaImage);

            $mUser->loadById($user['id']);
            $mUser->update([
                'avatar' => $avaImageName
            ]);
        }

        $this->output->writeln("<info>Avatars</info> has been generated!");
    }
}
