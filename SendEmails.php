<?php

/*
    Необходимо доработать класс рассылки Newsletter, что бы он отправлял письма
    и пуш нотификации для юзеров из UserRepository.

    За отправку имейла мы считаем вывод в консоль строки: "Email {email} has been sent to user {name}"
    За отправку пуш нотификации: "Push notification has been sent to user {name} with device_id {device_id}"

    Так же необходимо реализовать функциональность для валидации имейлов/пушей:
    1) Нельзя отправлять письма юзерам с невалидными имейлами
    2) Нельзя отправлять пуши юзерам с невалидными device_id. Правила валидации можете придумать сами.
    3) Ничего не отправляем юзерам у которых нет имен
    4) На одно и то же мыло/device_id - можно отправить письмо/пуш только один раз

    Для обеспечения возможности масштабирования системы (добавление новых типов отправок и новых валидаторов),
    можно добавлять и использовать новые классы и другие языковые конструкции php в любом количестве.
    Реализация должна соответствовать принципам ООП
*/

class Newsletter
{
    private UserRepository $userRepository;
    private UserDB $userDB;
    private Validator $validator;


    public function __construct($userRepository, $userDB, $validator)
    {
        $this->userRepository = $userRepository;
        $this->userDB = $userDB;
        $this->validator = $validator;

    }

    public function send(): void
    {
        $users = [];
        $userData = $this->userRepository->getUsers();

        $userNotificationsStorage = $this->userDB->userNotifications;
        $userEmailsStorage = $this->userDB->userEmails;

        foreach ($userData as $key => $user) {
            if (isset($user['email'])) {
                if ($this->validator->emailValidator($user['email'])) {
                    if (in_array($user['email'], $userEmailsStorage)) {
                        echo "You can send email only 1 time to this user";
                    } else {
                        echo "Email " . $user['email'] . " has been sent to user " . $user['name'] . "\n";
                        array_push($userNotificationsStorage, $user['email']);
                        print_r($userEmailsStorage);
                    }
                }
            }


            if (isset($user['device_id']) && !empty($user['device_id'])) {
                if ($this->validator->deviceIdValidator($user['device_id'])) {
                    if (in_array($user['device_id'], $userNotificationsStorage)) {
                        echo "You can send push notification only 1 time to this user";
                    } else {
                        echo "Push notification has been sent to user " . $user['name'] . " with device_id " . $user['device_id'] . "\n";
                        array_push($userNotoficationsStorage, $user['device_id']);
                        print_r($userNotoficationsStorage);
                    }

                }
            }
        }
    }
}

class Validator
{

    private string $deviceRules = '/^[a-zA-Z0-9]*$/';
    private string $emailRules = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';

    public function emailValidator($email): bool
    {
        return preg_match($this->emailRules, $email);
    }

    public function deviceIdValidator($deviceId): bool
    {
        return preg_match($this->deviceRules, $deviceId);
    }
}

class UserDB
{
    public array $userEmails = [];
    public array $userNotifications = [];

}


class UserRepository
{

    public function getUsers(): array
    {
        return [
            [
                'name' => 'Ivan',
                'email' => 'ivan@test.com',
                'device_id' => 'Ks[dqweer4'
            ],
            [
                'name' => 'Peter',
                'email' => 'peter@test.com'
            ],
            [
                'name' => 'Mark',
                'device_id' => 'Ks[dqweer4'
            ],
            [
                'name' => 'Nina',
                'email' => '...'
            ],
            [
                'name' => 'Luke',
                'device_id' => 'vfehlfg43g'
            ],
            [
                'name' => 'Zerg',
                'device_id' => ''
            ],
            [
                'email' => '...',
                'device_id' => ''
            ]
        ];

    }

}

$userRepository = new UserRepository();
$userDB = new UserDB();
$validator = new Validator();
$newsletter = new Newsletter($userRepository, $userDB, $validator);
$newsletter->send();