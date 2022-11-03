@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">組織架構</h2>

    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>

                        <th scope="col">部門</th>
                        <th scope="col">組別</th>
                        <th scope="col">主管</th>
                        <th scope="col">副主管</th>
                        <th scope="col" class="text-center">編輯</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $data)
                        <tr>
                            <td>{{ $data->department_title }}</td>
                            <td></td>
                            <td>{{ $data->a_name }}</td>
                            <td>{{ $data->a_name2 }}</td>
                            <td>
                                @can('cms.sale_channel.edit')
                                    <a href="{{ Route('cms.organize.edit', ['id' => $data->department_id]) }}"
                                        data-bs-toggle="tooltip" title="編輯"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                        @foreach ($data->group as $group)
                            <tr>
                                <td></td>
                                <td>{{ $group->group_title }}</td>
                                <td>{{ $group->b_name }}</td>
                                <td>{{ $group->b_name2 }}</td>
                                <td>
                                    @can('cms.sale_channel.edit')
                                        <a href="{{ Route('cms.organize.edit', ['id' => $group->group_id], true) }}"
                                            data-bs-toggle="tooltip" title="編輯"
                                            class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@once
    @push('sub-scripts')
    @endpush
@endonce
