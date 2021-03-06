<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use App\Level;
use App\Task;
use App\Tag;

class TasksController extends Controller
{

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}
	/**
	 * @param $id
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function fromCategory($id)
	{
		$category = Category::find($id);
		$tasks = Task::where('category_id',$id)->where('active',true)->orderBy('created_at')->get();

		return view('tasks.from_category', ['category' => $category, 'tasks' => $tasks]);
	}

	public function categories()
	{
		$categories = Category::orderBy('name')->get();

		return view('tasks.categories', ['categories' => $categories]);
	}

	/**
	 * @param $id
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function details($id)
	{
		$task = Task::find($id);
		$files = @scandir('uploads/tasks/'.$task->id);

		return view('tasks.details', ['id' => $id, 'task'=>$task, 'files'=>$files]);
	}

	/**
	 * @param Request $request
	 * @param $id
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function edit_task(Request $request, $id)
	{
		$categories = Category::orderBy('name')->get();
		$levels     = Level::orderBy('level')->get();
		$task       = Task::findOrFail($id);

		if($request->isMethod('post'))
		{
			$task->title       = $request->input('title');
			$task->description = $request->input('description');
			$task->target      = $request->input('target');
			$task->level_id    = $request->input('level');
			$task->points      = $request->input('points');
			$task->time        = $request->input('time');
			$task->category()->associate(Category::find($request->input('category')));
			$task->save();

			if(!empty($request->input('tags')))
			{
				$tags = explode(" ", trim($request->input('tags')));
				$task->tags()->detach();

				foreach ($tags as $tag)
				{
					$sample_tag = Tag::where('name', $tag)->first();
					if(empty($sample_tag))
					{
						$new_tag       = new Tag();
						$new_tag->name = $tag;
						$new_tag->save();
						$t = $new_tag;
					}
					else
					{
						$t = Tag::find($sample_tag['id']);
					}

					$task->tags()->attach($t->id);
				}
			}

			return redirect()->route('task_details',$id);
		}

		return view('tasks.edit', ['categories' => $categories, 'levels' => $levels, 'task'=> $task]);
	}

	public function new_task(Request $request)
	{

		$categories = Category::orderBy('name')->get();
		$levels     = Level::orderBy('level')->get();

		if($request->isMethod('post'))
		{

			$task              = new Task();
			$task->title       = $request->input('title');
			$task->active      = 1;
			$task->description = $request->input('description');
			$task->target      = $request->input('target');
			$task->level_id    = $request->input('level');
			$task->points      = $request->input('points');
			$task->time        = $request->input('time');
			$task->category()->associate(Category::find($request->input('category')));
			$task->save();

			if(!empty($request->input('tags')))
			{
				$tags = explode(" ", trim($request->input('tags')));

				foreach ($tags as $tag)
				{
					$sample_tag = Tag::where('name', $tag)->first();
					if(empty($sample_tag))
					{
						$new_tag       = new Tag();
						$new_tag->name = $tag;
						$new_tag->save();
						$t = $new_tag;
					}
					else
					{
						$t = Tag::find($sample_tag['id']);
					}

					$task->tags()->attach($t->id);
				}
			}
			else

			if($request->hasFile('target_file'))
			{
				if($request->file('target_file')->isValid())
				{
					$request->file('target_file')->move('uploads/tasks/'.$task->id,'cel_zadania.'.$request->file('target_file')->getClientOriginalExtension());
				}
			}
		}

		return view('tasks.new_task', ['categories' => $categories, 'levels' => $levels]);
	}
}
