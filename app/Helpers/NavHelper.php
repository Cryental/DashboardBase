<?php

namespace App\Helpers;

class NavHelper
{
    public function GetNavLinks($currentPage, $lastPage): array
    {
        if ($lastPage <= 5) {
            $links = [];
            $links[] = 1;
            for ($i = 1; $i <= $lastPage; $i++) {
                $links[] = $i;
            }
            $links[] = $lastPage;

            return $links;
        } else {
            if ($lastPage > 100) {
                $lastPage = 100;
            }

            if ($currentPage <= 2) {
                return [
                    1,
                    1,
                    2,
                    3,
                    4,
                    5,
                    $currentPage <= 1 ? 2 : $currentPage + 1,
                ];
            }
            if ($currentPage >= $lastPage - 1) {
                return [
                    $currentPage >= $lastPage ? $lastPage - 1 : $currentPage - 1,
                    $lastPage - 4,
                    $lastPage - 3,
                    $lastPage - 2,
                    $lastPage - 1,
                    $lastPage,
                    $lastPage,
                ];
            }

            return [
                $currentPage - 1,
                $currentPage - 2,
                $currentPage - 1,
                $currentPage,
                $currentPage + 1,
                $currentPage + 2,
                $currentPage + 1,
            ];
        }
    }
}
