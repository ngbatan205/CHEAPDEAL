<?php

class PackageController extends Controller
{
    public function index(): void
    {
        $model = new Package($this->db);
        $category = $this->input('category');
        $search = $this->input('q');

        $this->view('package/index', [
            'title' => 'Packages',
            'packages' => $model->all($category ?: null, $search),
            'featured' => $model->featured(),
            'categories' => $model->categories(),
            'selectedCategory' => $category,
            'search' => $search
        ]);
    }

    public function detail(): void
    {
        $package = (new Package($this->db))
            ->find((int) $this->input('id'));

        if (!$package) {
            http_response_code(404);
        }

        $this->view('package/detail', [
            'title' => $package['package_name'] ?? 'Package not found',
            'package' => $package
        ]);
    }
}