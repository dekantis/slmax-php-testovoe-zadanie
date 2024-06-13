<?php
declare(strict_types=1);

require_once 'Person.php';
require_once 'PeopleList.php';

$newPersonData = [
    'name' => 'John',
    'surname' => 'Doe',
    'birthdate' => '1990-01-01',
    'gender' => 1,
    'city' => 'New York',
];

$searchId = 6;

//Добавление человека в базу данных
$newPerson = new Person($newPersonData);
echo '----------------' . PHP_EOL;
echo 'ID добавленного человека: ' . $newPerson->id . PHP_EOL;
echo '----------------' . PHP_EOL.PHP_EOL;

//Удаление человека из бд
$newPerson->delete();
echo '----------------' . PHP_EOL;
echo 'Человек с ID: ' . $newPerson->id . ' удален' .PHP_EOL;
echo '----------------' . PHP_EOL.PHP_EOL;

//поиск человека по ID (с форматированными данными)
$person = (new Person($searchId))->formatPerson(true, true);
echo '----------------' . PHP_EOL;
echo 'Человек с ID: ' . $person->id . PHP_EOL;
echo 'Имя: ' . $person->name . PHP_EOL;
echo 'Фамилия: ' . $person->surname . PHP_EOL;
echo 'Датарождения: ' . $person->birthdate . PHP_EOL;
echo 'Возраст: ' . $person->age . PHP_EOL;
echo 'Пол: ' . $person->gender . PHP_EOL;
echo '----------------' . PHP_EOL.PHP_EOL;

$searchIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

//Списки людей
//поиск людей по ID
$personList = (new PeopleList($searchIds));
echo '----------------' . PHP_EOL;
echo 'По запросу c использованием массива ('
    . implode(',', $personList->peopleIds)
    . ')было найдено '
    . count($personList->peopleIds)
    . 'записи (' . implode(',', $personList->peopleIds)
    . ')'
    . PHP_EOL;
echo '----------------' . PHP_EOL.PHP_EOL;


//получение массива с объектами людей
echo '----------------' . PHP_EOL;
echo "<pre>". PHP_EOL;
var_dump($personList->getPeople());
echo "</pre>". PHP_EOL;
echo '----------------' . PHP_EOL.PHP_EOL;

$expression = '!=';
$searchId = 6;

//поиск с использованием операторов
$personList = (new PeopleList($searchId, $expression));
echo '----------------' . PHP_EOL;
echo 'По запросу с использованием оператора id'
    . $expression . $searchId
    . PHP_EOL;
echo "<pre>". PHP_EOL;
var_dump($personList->getPeople());
echo "</pre>". PHP_EOL;
echo '----------------' . PHP_EOL.PHP_EOL;

//удаление списка людей
//$personList->deletePeople();
