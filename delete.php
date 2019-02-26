public function deleteArticle($id){
        $article = Articles::where('article_id', $id)->first();
        if($article->articleDetail->article_image != null) {
            $image_path = public_path($article->article_image);
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        if(count($article->articleImages) > 0){
            foreach($article->articleImages as $image){
                $image_path = public_path($image);
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }
        $article->delete();
        return redirect()->route('adminpanel.articles')->with('message_variable', 'success')
            ->with('message', 'Blog Yazýsý Baþarýyla Silindi');
    }