<?php

#[Attribute]
class IsString
{
}


//#[Attribute(Attribute::TARGET_PROPERTY)]
//class StringAttr {}

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotEmpty
{
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class Positive
{
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsBool
{
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsInt
{
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsArray
{
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsObject
{
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsDate
{
}      // expects valid date string or DateTime

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsDateComplete
{
}      // expects valid date string or DateTime


#[Attribute(Attribute::TARGET_PROPERTY)]
class IsMoreThan
{
    public function __construct(public float|int $minValue)
    {
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class Max
{
    public function __construct(public float|int $maxValue)
    {
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class Min
{
    public function __construct(public float|int $minValue)
    {
    }
}

class ValidationResult
{
    public bool $success;
    public array $error;
    public object $data;

    public function __construct(bool $success, array $error, object $data)
    {
        $this->success = $success;
        $this->error = $error;
        $this->data = $data;
    }
}

class ValidationException extends Exception
{
}


class Validator
{
    public static function validate(object $obj, bool $isThrown = false): ValidationResult
    {
        $errors = [];
        $reflect = new ReflectionClass($obj);

        foreach ($reflect->getProperties() as $prop) {
            $prop->setAccessible(true);
            $value = $prop->getValue($obj);

            foreach ($prop->getAttributes() as $attr) {
                $name = $attr->getName();
                $args = $attr->getArguments();
                $msg = null;

                switch ($name) {

                    case IsString::class:
                        if (!is_string($value)) $msg = "{$prop->name} must be a string";
                        break;

                    case NotEmpty::class:
                        if (empty($value)) $msg = "{$prop->name} should not be empty";
                        break;

                    case Positive::class:
                        if (!is_numeric($value) || $value <= 0) $msg = "{$prop->name} must be positive";
                        break;

                    case IsBool::class:
                        if (!is_bool($value)) $msg = "{$prop->name} must be a boolean";
                        break;

                    case IsInt::class:
                        if (!is_int($value)) $msg = "{$prop->name} must be an integer";
                        break;

                    case IsArray::class:
                        if (!is_array($value)) $msg = "{$prop->name} must be an array";
                        break;

                    case IsObject::class:
                        if (!is_object($value)) $msg = "{$prop->name} must be an object";
                        break;

                    case IsDate::class:
                        $d = DateTime::createFromFormat('Y-m-d', (string)$value);
                        if (!$d || $d->format('Y-m-d') !== (string)$value) {
                            $msg = "{$prop->name} must be a valid date (Y-m-d)";
                        }
                        break;

                    case IsDateComplete::class:
                        if (!($value instanceof DateTime)) $msg = "{$prop->name} must be a DateTime instance.";
                        break;

                    case IsMoreThan::class:
                        [$min] = $args;
                        if (!is_numeric($value) || $value <= $min) {
                            $msg = "{$prop->name} must be more than $min";
                        }
                        break;

                    case Max::class:
                        [$max] = $args;
                        if (!is_numeric($value) || $value > $max) {
                            $msg = "{$prop->name} must be at most $max";
                        }
                        break;

                    case Min::class:
                        [$min2] = $args;
                        if (!is_numeric($value) || $value < $min2) {
                            $msg = "{$prop->name} must be at least $min2";
                        }
                        break;

                }

                if ($msg !== null) {
                    if ($isThrown) throw new ValidationException($msg);
                    $errors[] = $msg;
                }
            }
        }

        return new ValidationResult(empty($errors), $errors, $obj);
    }
}

//class User
//{
//    #[IsString]
//    #[NotEmpty]
//    public $name;
//
//    #[IsInt]
//    #[Positive]
//    public $age;
//
//    #[IsBool]
//    public $marred;
//
//    #[IsDate]
//    public $birthday;
//
//    #[Min(0)]
//    #[Max(100)]
//    public $score;
//
//    public function __construct($name, $age, $marred, $birthday, $score)
//    {
//        $this->name = $name;
//        $this->age = $age;
//        $this->marred = $marred;
//        $this->birthday = $birthday;
//        $this->score = $score;
//    }
//}
//
//print_r('is true' . PHP_EOL);
//$user = new User(
//    "Alice",          // StringAttr + NotEmpty ✅
//    25,               // IsInt + Positive ✅
//    false,            // IsBool ✅
//    "1995-07-20",     // IsDate (valid Y-m-d) ✅
//    80                // Min(0) + Max(100) ✅
//);
//
//$valid = Validator::validate($user);
//
//if ($valid->success) {
//    echo "Validation passed!";
//    print_r($valid->data);
//} else {
//    echo "Validation errors:\n";
//    print_r($valid->error);
//}
//
//print_r('is false' . PHP_EOL);
//$user = new User("", "twenty", "yes", "2025-02-30", 150);
//$valid = Validator::validate($user);
//
//if (!$valid->success) {
//    print_r($valid->error);
//}
//
