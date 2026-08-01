<?php

class OfferController extends Controller
{
    public function index(): void
    {
        $this->view('offer/index', [
            'title' => 'Special offers',
            'offers' => (new Offer($this->db))->active(),
        ]);
    }
}
