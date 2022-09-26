<form id="search" action="{{ Route($routeName) }}" method="GET">
    <div class="card shadow p-4 mb-4">
        <div class="row">
            <fieldset class="col-12 mb-3">
                <legend class="col-form-label p-0 mb-2">角色篩選</legend>
                <div class="px-1 pt-1">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="roles" id="file1" value='1'
                                aria-label="角色篩選">
                        <label class="form-check-label" for="file1">已設定角色</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="roles" id="file2" value='2'
                                aria-label="角色篩選">
                        <label class="form-check-label" for="file2">未設定角色</label>
                    </div>
                </div>
            </fieldset>

            <div class="col-12 mb-3">
                <label class="form-label" for="select2">角色搜尋</label>
                <select name="select2[]" id="select2" class="-select2 -single form-select" data-placeholder="請單選">
                    <option value="" selected disabled>請選擇</option>
                    <option value="1">item 1</option>
                    <option value="2">item 2</option>
                    <option value="3">item 3</option>
                </select>
            </div>

            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">姓名</label>
                <input class="form-control" type="text" name="name" placeholder="請輸入姓名" value=""
                       aria-label="姓名">
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">帳號</label>
                <input class="form-control" type="text" name="account" placeholder="請輸入帳號" value=""
                       aria-label="帳號">
            </div>
        </div>
        <div class="col">
            <button type="submit" class="btn btn-primary px-4">搜尋</button>
        </div>
    </div>
</form>
