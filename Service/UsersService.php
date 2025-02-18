<?php

namespace Tpf\Service;

use PHPMailer\PHPMailer\PHPMailer;
use Tpf\Model\User;
use Tpf\Service\Auth\PasswordHasher;


class UsersService
{
    public static function registerUser(string $username, string $password, string $email, ?string $firstName = null, ?string $lastName = null): User
    {
        global $TPF_CONFIG;
        if (!isset($TPF_CONFIG['secret'])) {
            $TPF_CONFIG['secret'] = md5('changeme');
        }

        $user = new User();
        $user->username = $username;
        $user->password = $password;
        $user->email = $email;
        $user->firstname = $firstName;
        $user->lastname = $lastName;
        $user->registeredAt = new \Datetime();
        $user->activationToken = sha1($user->password . $TPF_CONFIG['secret']);
        PasswordHasher::hashPassword($user);
        $user->save();

        return $user;
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \Exception
     */
    private static function sendActivationEmail(User $user): void
    {
        if (!isset($TPF_CONFIG['email']) || !isset($TPF_CONFIG['email']['from'])) {
            throw new \Exception("Sender email is not configured");
        }
        $subject = 'Welcome to Tiny PHP Framework CMS!';
        $link = $_SERVER['HTTP_HOST'] . '/activate?user=' . $user->id . '&token=' . $user->activationToken;
        $text = 'Welcome to Tiny PHP Framework CMS! To activate your account please open the following link: ' . $link;

        MailService::sendMail($user->email, trim($user->firstname), $subject, $text);
    }

    public static function activateUser(User $user): void
    {
        $user->isActive = true;
        $user->save();
    }

    public static function deactivateUser(User $user): void
    {
        $user->isActive = false;
        $user->save();
    }

    public static function updateProfile(int|string $id, array $data): void
    {
        global $TPF_CONFIG;
        if (!isset($TPF_CONFIG['secret'])) {
            $TPF_CONFIG['secret'] = md5('changeme');
        }

        $user = User::load($id);
        if (!$user) {
            throw new \Exception("User not found");
        }

        if (isset($data['username']) && !empty(trim($data['username']))) {
            $user->username = $data['username'];
        }
        if (isset($data['email'])) {
            $user->email = $data['email'];
        }
        if (isset($data['firstname'])) {
            $user->firstname = $data['firstname'];
        }
        if (isset($data['lastname'])) {
            $user->lastname = $data['lastname'];
        }
        if (isset($data['photo'])) {
            $user->photo = $data['photo'];
        }
        if (isset($data['password']) && !empty($data['password']) &&
              isset($data['password_confirm']) && !empty($data['password_confirm']) &&
              $data['password'] == $data['password_confirm']) {
            $user->password = $data['password'];
            PasswordHasher::hashPassword($user);
        }
        $user->save();
    }

    public static function getRoles(): array
    {
        return [User::ROLE_CLIENT => 'Client', User::ROLE_EDITOR => 'Editor', User::ROLE_ADMIN => 'Admin'];
    }
}