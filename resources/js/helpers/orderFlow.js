module.exports = class OrderFlow {
    static getDataFlow(id) {
        let url = Laravel.apiUrl.flowList;
        url += '/' + id;

        return axios.get(url).then((re) => {
            if (re.status === 200 && re.data.status === '0') {
                // console.log(re.data.datas);
                // 排序: created_at 由新到舊
                (re.data.message).sort((a, b) => (
                    new Date(b.created_at) - new Date(a.created_at)
                ));
                return re.data.message;
            } else {
                return Promise.reject(re);
            }
        });
    }

    static flowListHtml(datalist) {
        let result = '';
        datalist.forEach(data => {
            let li = '<li>';

            li += '<h6>' + data.created_at + '</h6>';
            li += '<p>';
            li += '<span>物態：' + data.status_title + '</span>';
            li += '<span>' + data.user_type + '：' + data.name;
            if (data.deliveryman2_name) { li += '、' + data.deliveryman2_name; }
            li += '</span>';
            li += '</p>';
            if (data.memo) {
                li += '<p>備註：' + data.memo + '</p>';
            }

            li += '</li>';
            result += li;
        });
        return result;
    }
};