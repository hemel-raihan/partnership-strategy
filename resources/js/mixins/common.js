import Swal from 'sweetalert2'

const {format} = require('number-currency-format');
import moment from 'moment'

export const Common = {
    data() {
        return {
            data: {},
        }
    },
    methods: {
        config() {
            let token = localStorage.getItem('token');
            return {
                headers: {Authorization: `Bearer ${token}`}
            };
        },
        axiosGet(route, success, error) {
            axios.get(this.mainOrigin + 'api/' + route, this.config())
                .then((response) => {
                    success(response.data);
                }).catch((err) => {
                if (err.response.status === 401) {
                    localStorage.setItem("token", "");
                    this.redirect(this.mainOrigin + 'login');
                } else {
                    error(err);
                }
            });
        },
        axiosGetWithoutToken(route, success, error) {
            axios.get(this.mainOrigin + 'api/' + route)
                .then((response) => {
                    success(response.data);
                }).catch((err) => {
                error(err);
            });
        },
        axiosPost(route, data, success, error) {
            axios.post(this.mainOrigin + 'api/' + route, data, this.config())
                .then((response) => {
                    success(response.data);
                }).catch((err) => {
                console.log(err)
                if (err.response.status === 401) {
                    localStorage.setItem("token", "");
                    this.redirect(this.mainOrigin + 'login');
                } else {
                    error(err);
                }
            });
        },
        axiosPostWithoutToken(route, data, success, error) {
            axios.post(this.mainOrigin + 'api/' + route, data)
                .then((response) => {
                    success(response.data);
                }).catch((err) => {
                error(err);
            });
        },
        axiosDelete(route, id, success, error) {
            axios.delete(this.mainOrigin + 'api/' + route + '/' + id, this.config())
                .then((response) => {
                    success(response.data);
                }).catch((err) => {
                if (err.response.status === 401) {
                    localStorage.setItem("token", "");
                    this.redirect(this.mainOrigin + 'login');
                } else {
                    error(err);
                }
            });
        },
        axiosPut(route, data, success, error) {
            axios.put(this.mainOrigin + 'api/' + route, data, this.config())
                .then((response) => {
                    success(response.data);
                }).catch((err) => {
                if (err.response.status === 401) {
                    localStorage.setItem("token", "");
                    this.redirect(this.mainOrigin + 'login');
                } else {
                    error(err);
                }
            });
        },
        redirect(route) {
            window.location.href = route;
        },
        successNoti(msg) {
            this.$toaster.success(msg)
        },
        errorNoti(msg) {
            if (msg.response === undefined) {
                this.$toaster.error(msg);
            } else if (msg.response.data.message === undefined) {
                this.$toaster.error(msg);
            } else {
                this.$toaster.error(msg.response.data.message);
            }
        },
        numberFormat(value) {
            if (value == null) {
                return '';
            } else {
                return format(value, {
                    currency: '',
                    spacing: false,
                    currencyPosition: 'LEFT'
                });
            }
        },
        weightFormat(value) {
            return format(value, {
                currency: ' Kg.',
                spacing: false,
                currencyPosition: 'right'
            })
        },
        dateFormat(date) {
            return date ? moment(date, 'YYYY-MM-DD').format("DD-MM-YYYY") : '';
        },
        dateTimeFormat(date) {
            return date ? moment(date, 'YYYY-MM-DD h:mm:ss').format("DD-MM-YYYY h:mm a") : '';

        },
        timeFormat(date) {
            return date ? moment(date, 'YYYY-MM-DD h:mm:ss').format("h:mm a") : '';
        },
        periodFormat(data) {
            return data ? moment(data).format('MM-YYYY') : ''
        },
        deleteAlert(callback) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.value) {
                    callback();
                }
            })
        },
        confirmationAlert(callback, title,text ="You won't be able to revert this!") {
            Swal.fire({
                title: title,
                text: text,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Confirm!'
            }).then((result) => {
                if (result.value) {
                    callback();
                }
            })
        },
        printInvoiceAlert(callback, title,text ="You won't be able to revert this!") {
            Swal.fire({
                title: title,
                text: text,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Confirm!'
            }).then((result) => {
                if (result.value) {
                    callback('confirm');
                }
                else{
                    callback('cancel');
                }
            })
        },
        bagAlert(callback, title,text ="You won't be able to revert this!") {
            Swal.fire({
                title: title,
                text: text,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'দিচ্ছি',
                cancelButtonText: 'লাগবে না',
                allowOutsideClick: false,
                allowEscapeKey: false,
                // onOpen: () => {
                //     let myButton = document.getElementsByClassName("swal2-confirm");
                //     console.log(myButton)
                //     if (document.activeElement === myButton) {
                //       console.log("The button is focused");
                //     }
                //       // $(".swal2-confirm").css("border",'5px solid yellow')
                // }
            }).then((result) => {
                if (result.value) {
                    callback('confirm');
                }
                else{
                    callback('cancel');
                }
            })
        },
        loyaltyAlert(callback, title,text ="You won't be able to revert this!") {
            Swal.fire({
                title: title,
                text: text,
                icon: 'info',
                showCancelButton: true,
                // focusCancel: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                allowOutsideClick: false,
                allowEscapeKey: false,
                onOpen: () => {
                    // document.removeEventListener('keydown', this.handleKeyDown);
                },
            }).then((result) => {
                callback(result);
            })
        },
        infoAlert(title, message) {
            Swal.fire({
                title: title,
                text: message,
                icon: 'info',
                showCancelButton: false,
                showConfirmButton: true,
                // cancelButtonColor: '#d33',
                // cancelButtonText: 'Close',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Close',
            })
        },
        processText(value) {
            let rex = /([A-Z])([A-Z])([a-z])|([a-z])([A-Z])/g;
            value = value.replace(rex, '$1$4 $2$3$5');
            value = value.replace('-', ' ');
            return value;
        }
    },
}
