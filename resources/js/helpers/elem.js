module.exports = class Elem {
    static renderSelect(elem, arrVal, options) {
        // elem.html('');
        options = options ? options : {};

        let _default = options.default ? options.default : "";
        let _key = options.key ? options.key : "key";
        let _value = options.value ? options.value : "value";
        let _defaultOption = options.defaultOption ? options.defaultOption : "請選擇";
        let output = arrVal
            .map((v) => {
                let selected = v[_key] == _default ? "selected" : "";

                return (
                    '<option value="' +
                    v[_key] +
                    '" ' +
                    selected +
                    ">" +
                    v[_value] +
                    "</option>"
                );
            })
            .join("");

        if (_defaultOption) {
            output = "<option value=''>" + _defaultOption + "</option>" + output;
        }
        elem.html(output);
    }
};
