<?php
declare(strict_types=1);

class PeopleList
{
    const EXPRESSIONS = ['=' , '>', '<', '!='];

    /**
     * @var array
     */
    public array $peopleIds;

    /**
     * @var PDO
     */
    private PDO $db;

    /**
     * @param mixed $ids
     * @param string $expression
     * @throws Exception
     */
    public function __construct(mixed $ids, string $expression = '=')
    {
        if (!class_exists('Person')) {
            throw new Exception('class Person expected');
        }

        $this->db = new PDO('sqlite:' . Person::DB_FILE);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (!in_array($expression, self::EXPRESSIONS)) {
            throw new Exception('Invalid expression: ' . $expression);
        }

        $whereStatement = match (true) {
            is_array($ids) => ' IN (' . implode(',', $ids) . ')',
            is_integer($ids) => $expression . $ids,
            default => throw new Exception('Invalid IDs Type'),
        };

        $result = $this->db->prepare('SELECT id FROM ' . Person::TABLE .  ' WHERE id' . $whereStatement);
        $result->execute();

        $this->peopleIds = $result->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getPeople(): array
    {
        $result = [];

        foreach ($this->peopleIds as $id) {
            $result[] = new Person($id);
        }

        return $result;
    }


    /**
     * @return void
     * @throws Exception
     */
    public function deletePeople(): void
    {
        try {
            foreach ($this->peopleIds as $id) {
                (new Person($id))->delete();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}