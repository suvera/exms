<?php

declare(strict_types=1);

namespace dev\suvera\exms\repo;

use dev\suvera\exms\data\Student;
use dev\winterframework\pdbc\PdbcTemplate;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\Component;

#[Component]
class StudentRepository {
    #[Autowired]
    private PdbcTemplate $pdbc;

    public function createStudent(Student $student) {
        return 'createStudent';
    }
}
