export default {
    methods: {
        async formRequest(type, url, body) {
            let response = {};
            let errors = {};

            try {
                switch (type) {
                    case "POST":
                        response = await axios.post(url, body);
                        break;
                    case "PUT":
                        response = await axios.put(url, body);
                        break;
                }
            } catch (error) {
                if (error.response) {
                    const errorMessages = error.response.data.errors;
                    const errorMessage = error.response.data.message;

                    if (errorMessages) {
                        Object.keys(errorMessages).forEach(key => {
                            const message = Array.isArray(errorMessages[key])
                                ? errorMessages[key].join(', ')
                                : errorMessages[key];
    
                            errors[key] = message;
                        });
                    }

                    if (errorMessage) {
                        errors['field'] = errorMessage;
                    }
                }
            }

            return {
                response,
                errors,
            };
        },
        async request(type, url, options = {}) {
            let response = {};
            let errorMessage  = '';

            try {
                switch (type) {
                    case "GET":
                        response = await axios.get(url, options);
                        break;
                    case "DELETE":
                        response = await axios.delete(url);
                        break;
                }
            } catch (error) {
                if (error.response) {
                    const message = error.response.data.message;
                    errorMessage = message;
                }
            }

            return {
                response,
                error: errorMessage,
            };
        },
    },
};
