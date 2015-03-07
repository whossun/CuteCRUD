<?php namespace App\Http\Controllers;

use Session;
use Request;
use Validator;
use App\Utils;
use App\Models\Table;

class CRUDController extends Controller
{

    /**
     * Show the Index Page
     *
     * @Get("/", as="index")
     */
    public function index()
    {
        $rows = Table::all();
        return view('index', compact('rows'));
    }

    /**
     * Show Create Page
     *
     * @Get("crud/create", as="crud.create")
     */
    public function create()
    {
        return view('crud.create');
    }

    /**
     * Update curd
     *
     * @Post("crud/update/{id}", as="crud.update")
     */
    public function update($id)
    {
        //TODO: use middleware
        $request_data = Request::all();
        $request_data['creatable'] = Request::has('creatable');
        $request_data['editable']  = Request::has('editable');
        $request_data['listable']  = Request::has('listable');
        $request_data['slug']      = str_slug(Request::get('table_name'));

        $v = Validator::make($request_data, Table::$edit_rules);

        if ($v->fails()) {
            Session::flash('error_msg',Utils::buildMessages($v->errors()->all()));
            return redirect("/crud/edit/".$id)->withErrors($v)->withInput();
        }

        if( 0 != Table::where('table_name', Request::get('table_name'))->where('id','!=',$id)->count()){
            Session::flash('error_msg','Table name already exist');
            return redirect("/crud/edit/".$id)->withInput();
        }

        Table::find($id)->update($request_data);

        Session::flash('success_msg','CRUD updated successfully');
        return redirect()->route('index');
    }

    /**
     * Show Edit Page
     *
     * @Get("crud/edit/{id}", as="crud.edit")
     */
    public function edit($id)
    {
        $crud = Table::find($id);
        return view('crud.edit', compact('crud'));
    }

    /**
     * Store crud
     *
     * @Post("crud/create", as="crud.store")
     */
    public function store()
    {
        //TODO: use middleware
        $request_data = Request::all();
        $request_data['creatable'] = Request::has('creatable');
        $request_data['editable']  = Request::has('editable');
        $request_data['listable']  = Request::has('listable');
        $request_data['slug']      = str_slug(Request::get('table_name'));

        $v = Validator::make(Request::all(), Table::$rules);

        if ($v->fails()) {
            Session::flash('error_msg', Utils::buildMessages($v->errors()->all()));
            return redirect()->back()->withErrors($v)->withInput();
        }

        Table::create($request_data);
        Session::flash('success_msg','CRUD created successfully');
        return redirect()->route('index');
    }

    /**
     * Delete crud
     *
     * @Get("crud/delete/{id}", as="crud.delete")
     */
    public function delete($id){
        Table::destroy($id);
        Session::flash('success_msg','CRUD deleted successfully');
        return redirect()->route('index')->withInput();
    }
}
