<?php

class DealController extends Controller
{
    public function index(): void
    {
        $filter = strtolower($this->input('type'));
        $dealType = match ($filter) {
            'double' => 'DoublePackage',
            'triple' => 'TriplePackage',
            default => null,
        };

        $this->view('deal/index', [
            'title' => 'Combo plans',
            'deals' => (new Deal($this->db))->all($dealType),
            'selectedType' => $filter,
        ]);
    }

    public function detail(): void
    {
        $deal = (new Deal($this->db))->find((int) $this->input('id'));
        if (!$deal) {
            http_response_code(404);
        }

        $this->view('deal/detail', [
            'title' => $deal['deal_name'] ?? 'Combo not found',
            'deal' => $deal,
        ]);
    }
}
