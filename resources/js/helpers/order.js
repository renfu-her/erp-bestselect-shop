module.exports = class Order {
    static getUsers(company_id) {
        let url = Laravel.apiUrl.getUsers;

        url = url.replace("_replace_", company_id);
    
        return axios.get(url).then((re) => {
            if (re.status === 200 && re.data.status === "0") {   
                return re.data.data;
            } else {
                return Promise.reject(re);
            }
        });
    }

    static getDepots(company_id) {
        let url = Laravel.apiUrl.getDepots;

        url = url.replace("_replace_", company_id);
    
        return axios.get(url).then((re) => {
            if (re.status === 200 && re.data.status === "0") {   
                return re.data.data;
            } else {
                return Promise.reject(re);
            }
        });
    }

    static getTemps(company_id) {
        let url = Laravel.apiUrl.getTemps;

        url = url.replace("_replace_", company_id);
    
        return axios.get(url).then((re) => {
            if (re.status === 200 && re.data.status === "0") {   
                return re.data.data;
            } else {
                return Promise.reject(re);
            }
        });
    }

    static getDims(company_id,temp_id) {
        let url = Laravel.apiUrl.getDims;

        url = url.replace("_replace_", company_id);
        url = url.replace("_replace2_", temp_id);
    
        return axios.get(url).then((re) => {
            if (re.status === 200 && re.data.status === "0") {   
                return re.data.data;
            } else {
                return Promise.reject(re);
            }
        });
    }
};
