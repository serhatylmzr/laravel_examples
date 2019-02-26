public function articles(){
        $breadcrumbs = [
            0 => [
                'title' => 'Anasayfa',
                'url' => route('adminpanel.homepage')
            ],
            1 => [
                'title' => 'Blog Yazýlarý',
                'url' => ''
            ]
        ];
        $articles = Articles::orderBy('created_at')->paginate(5);
        return view('adminpanel.articles.articles',compact('articles','breadcrumbs'));
}