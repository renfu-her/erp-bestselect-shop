module.exports = class Addr {
    static addrFormating(address) {
        let url = Laravel.apiUrl.addrFormating;
        url += "/" + address;
        return axios.get(url).then((re) => {
            if (re.status === 200 && re.data.status === "0") {
                // console.log(re.data.datas);
                return re.data;
            } else {
                return Promise.reject(re);
            }
        });
    }

    static getRegions($city_id, options) {
        let url = Laravel.apiUrl.getRegions;
        url += "/" + $city_id;

        if (options && options.can_service) {
            url += "?can_service=1";
        }

        return axios.get(url).then((re) => {
            if (re.status === 200 && re.data.status === "0") {
                // console.log(re.data.datas);
                return re.data;
            } else {
                return Promise.reject(re);
            }
        });
    }

    static getServiceAreaRegions($city_id, options) {
        let url = Laravel.apiUrl.getServiceAreaRegions;
        console.log(url);
        url += "/" + $city_id;

        if (options && options.service_area_id) {
            url += "?service_area_id=" + options.service_area_id;
        }

        return axios.get(url).then((re) => {
            if (re.status === 200 && re.data.status === "0") {
                // console.log(re.data.datas);
                return re.data;
            } else {
                return Promise.reject(re);
            }
        });
    }

    static getCharterCitys($city_id, car_id, temp_id) {
        let url = Laravel.apiUrl.getCharterCitys;
        url += "/" + $city_id + "/" + car_id + "/" + temp_id;

        return axios.get(url).then((re) => {
            if (re.status === 200 && re.data.status === "0") {
                // console.log(re.data.datas);
                return re.data;
            } else {
                return Promise.reject(re);
            }
        });
    }
};
