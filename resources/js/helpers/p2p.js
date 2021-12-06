module.exports = class P2p {
    static getP2pLocations(depot_id) {
        let url = Laravel.apiUrl.getP2pLocations;
        url += "/" + depot_id;
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
