<?php

require "validation.php";

class User
{
    #[IsString]
    #[NotEmpty]
    public $name;

    #[IsInt]
    #[Positive]
    public $age;

    #[IsBool]
    public $marred;

    #[IsDate]
    public $birthday;

    #[Min(0)]
    #[Max(100)]
    public $score;

    public function __construct($name, $age, $marred, $birthday, $score)
    {
        $this->name = $name;
        $this->age = $age;
        $this->marred = $marred;
        $this->birthday = $birthday;
        $this->score = $score;
    }
}

print_r('is true' . PHP_EOL);
$user = new User(
    "Alice",          // StringAttr + NotEmpty ✅
    25,               // IsInt + Positive ✅
    false,            // IsBool ✅
    "1995-07-20",     // IsDate (valid Y-m-d) ✅
    80                // Min(0) + Max(100) ✅
);

$valid = Validator::validate($user);

if ($valid->success) {
    echo "Validation passed!";
    print_r($valid->data);
} else {
    echo "Validation errors:\n";
    print_r($valid->error);
}

print_r('is false' . PHP_EOL);
$user = new User("", "twenty", "yes", "2025-02-30", 150);
$valid = Validator::validate($user);

if (!$valid->success) {
    print_r($valid->error);
}

