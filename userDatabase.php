<?php

require 'database.php';

class User
{

    public string $name;
    public int $age;
    public bool $marred;

    public function __construct(string $name, int $age, bool $marred)
    {
        $this->name = $name;
        $this->age = $age;
        $this->marred = $marred;
    }
}

$user = new User('alex sander ', 9, true);
$db = new Database();

// SELECT
$db->select('users', ['id', 'name'])
    ->where('id', 5)
    ->exec();

// INSERT
$db->insert('users')
    ->column('name', $user->name)
    ->column('age', $user->age)
    ->column('marred', $user->marred)
    ->exec();

print_r('------------' . PHP_EOL);
// INSERT
$db->insertClass('users', $user)
    ->exec();


// UPDATE
$db->updateClass('users', $user)
    ->where('id', 5)
    ->exec();



