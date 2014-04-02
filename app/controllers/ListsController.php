<?php
class ListsController extends BaseController {

	public function index()
	{
		$users = User::select('name', 'twitter_handle', 'klout_metric_score', 'image')
				->orderBy('klout_metric_score', 'desc')
				->where('location_id', '=', 1)
				->take(10)
				->get();
		$lists = Lists::paginate(4);

		return View::make('list.index')->withUsers($users)->withLists($lists);
	}
}