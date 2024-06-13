<?php
declare(strict_types=1);

class Person
{
    const DB_FILE = 'db.sqlite';
    const TABLE = 'people';
    const DATA_FIELDS = ['name', 'surname', 'birthdate', 'gender', 'city'];

    /**
     * @var int
     */
    public int $id;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $surname;

    /**
     * @var string
     */
    public string $birthdate;

    /**
     * @var int
     */
    public int $gender;

    /**
     * @var string|mixed
     */
    public string $city;

    /**
     * @var PDO
     */
    private PDO $db;

    /**
     * @param mixed $data
     * @throws Exception
     */
    public function __construct(mixed $data)
    {
        $this->db = new PDO('sqlite:' . self::DB_FILE);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        if (is_array($data) && $this->validate($data)) {
            $this->validate($data);

            $this->name = $data['name'];
            $this->surname = $data['surname'];
            $this->birthdate = $data['birthdate'];
            $this->gender = $data['gender'];
            $this->city = $data['city'];

            $this->save();
        }

        if (is_integer($data)) {
            $this->getById($data);
        }
    }

    /**
     * @return void
     */
    public function createTable(): void
    {
        $command = 'CREATE TABLE IF NOT EXISTS people (
                id INTEGER PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                surname VARCHAR(255) NOT NULL,
                birthdate VARCHAR(255) NOT NULL,
                gender BOOLEAN NOT NULL,
                city VARCHAR(255) NOT NULL
            )';

        $this->db->exec($command);
    }


    /**
     * @param $property
     * @param $value
     * @return void
     */
    protected function set($property, $value): void
    {
        if (property_exists($this, $property) && !isset($this->$property)) {
            $this->$property = in_array($property, ['id', 'gender']) ? (int) $value : $value;
        }
    }

    /**
     * @param array $data
     * @return bool
     * @throws Exception
     */
    private function validate(array $data): bool
    {
        if (empty($data)) {
            throw new Exception("Missing data");
        }

        foreach (self::DATA_FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                throw new Exception("Отсутсвую данные для поля '$field'");
            }

            if ((in_array($field, ['name', 'surname']) && !preg_match('/^[a-zA-Zа-яА-Я]+$/u', $data[$field]))
                || ($field === 'gender' && ($data[$field] < 0 || $data[$field] > 1))) {
                throw new Exception("Поле $field содержит недопустимые символы");
            }
        }

        return true;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function save(): void
    {
        try {
//            $this->createTable($db);
            $stmt = $this->db->prepare("INSERT INTO people (name, surname, birthdate, gender, city) VALUES (:name, :surname, :birthdate, :gender, :city)");
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':surname', $this->surname);
            $stmt->bindParam(':birthdate', $this->birthdate);
            $stmt->bindParam(':gender', $this->gender);
            $stmt->bindParam(':city', $this->city);
            $stmt->execute();
            $this->id = (int) $this->db->lastInsertId();
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * @return void
     */
    public function delete(): void
    {
        if (!isset($this->id)) {
            return;
        }

        $result = $this->db->prepare('DELETE FROM ' . self::TABLE .  ' WHERE id = ?');
        $result->execute(array($this->id));
    }

    /**
     * @param $birthdate
     * @return int
     */
    public static function ageFromBirthdate($birthdate): int
    {
        $diff = date_diff(date_create($birthdate), date_create('today'));
        return $diff->y;
    }

    /**
     * @param $gender
     * @return string
     */
    public static function genderText($gender): string
    {
        return $gender == 1 ? 'муж' : 'жен';
    }

    /**
     * @param int $id
     * @return self|null
     */
    private function getById(int $id): ?self
    {
        $result = $this->db->prepare('SELECT * FROM ' . self::TABLE .  ' WHERE id = ?');
        $result->execute(array($id));
        $data = $result->fetch();

        if (!$data) {
            return null;
        }

        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * @param $calculateAge
     * @param $convertGender
     * @return stdClass
     */
    public function formatPerson($calculateAge = false, $convertGender = false): stdClass
    {
        $person = new stdClass();
        if (!isset($this->id)) {
            return $person;
        }

        $person->id = $this->id;
        $person->name = $this->name;
        $person->surname = $this->surname;
        $person->birthdate = $this->birthdate;

        if ($calculateAge) {
            $person->age = self::ageFromBirthdate($this->birthdate);
        }

        if ($convertGender) {
            $person->gender = self::genderText($this->gender);
        } else {
            $person->gender = $this->gender;
        }

        $person->city = $this->city;

        return $person;
    }
}