<?php

namespace App\Http\Controllers;

use App\Services\VacancyMarketplace;

class PublicVacancyController extends Controller
{
    public function show(int $vacancy, VacancyMarketplace $marketplace)
    {
        return view(
            'vacancies.show',
            $marketplace->detail($vacancy)
        );
    }
}
