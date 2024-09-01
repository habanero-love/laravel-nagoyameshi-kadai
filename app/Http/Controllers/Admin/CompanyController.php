<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $company = Company::first();

        return view('admin.company.index', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('admin.company.edit', compact('company'));
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required',
            'postal_code' => 'sometimes|required|digits:7',
            'address' => 'sometimes|required',
            'representative' => 'sometimes|required',
            'establishment_date' => 'sometimes|required',
            'capital' => 'sometimes|required',
            'business' => 'sometimes|required',
            'number_of_employees' => 'sometimes|required',
        ]);

        $company = Company::first();

        $company->update($validatedData);

        return redirect()->route('admin.company.index')->with('flash_message', '会社概要を編集しました');
    }
}
