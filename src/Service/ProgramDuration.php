<?php

namespace App\Service;

use App\Entity\Program;

class ProgramDuration
{
    public function calculate(Program $program): string
    {
        $total = 0;
        foreach ($program->getSeasons() as $season) {
            foreach ($season->getEpisodes() as $episode) {
                $total += $episode->getDuration();
            }
        }
        $nbJours = floor($total / 1440);
        $reste = $total % 1440;
        $nbHeures = floor($reste / 60);
        $reste = $reste % 60;

        return "durée totale de la série: $nbJours jours, $nbHeures  heures et $reste minutes";
    }
}