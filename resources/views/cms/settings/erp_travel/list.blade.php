@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        團控查詢帳號</h2>
    <div class="card shadow p-4 mb-4">
        <div class="row mb-4">
            <div class="col">
                @can('cms.erp-travel.create')
                    <a href="{{ Route('cms.erp-travel.create', null, true) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 新增帳號
                    </a>
                @endcan
            </div>

        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">編輯</th>
                        <th scope="col">login_name</th>
                        <th scope="col">login_pw</th>
                        <th scope="col">login_ip</th>
                        <th scope="col">status</th>
                        <th scope="col">cf</th>
                        <th scope="col">ittms_code</th>
                        <th scope="col">agt_flag</th>
                        <th scope="col">flag_package</th>
                        <th scope="col">flag_ship</th>
                        <th scope="col">flag_tax</th>
                        <th scope="col">sales_type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>
                                @can('cms.erp-travel.edit')
                                    <a href="{{ Route('cms.erp-travel.edit', ['id' => $data['login_name']]) }}"
                                        data-bs-toggle="tooltip" title="編輯"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endcan
                            </td>
                            <td>{{ $data['login_name'] }}</td>
                            <td>{{ $data['login_pw'] }}</td>
                            <td>
                                @if (isset($data['login_ip']))
                                    {{ $data['login_ip'] }}
                                @endif
                            </td>
                            <td>{{ $data['status'] }}</td>
                            <td>
                                @if (isset($data['cf']))
                                    {{ $data['cf'] }}
                                @endif
                            </td>
                            <td>
                                @if (isset($data['ittms_code']))
                                    {{ $data['ittms_code'] }}
                                @endif
                            </td>
                            <td>
                                @if (isset($data['agt_flag']))
                                    {{ $data['agt_flag'] }}
                                @endif
                            </td>
                            <td>
                                @if (isset($data['flag_package']))
                                    {{ $data['flag_package'] }}
                                @endif
                            </td>
                            <td>
                                @if (isset($data['flag_ship']))
                                    {{ $data['flag_ship'] }}
                                @endif
                            </td>
                          
                            <td>
                                @if (isset($data['flag_tax']))
                                    {{ $data['flag_tax'] }}
                                @endif
                            </td>
                            <td>
                                @if (isset($data['sales_type']))
                                    {{ $data['sales_type'] }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
