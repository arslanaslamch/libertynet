if (typeof installer === 'undefined') {
    installer = {
        init: function () {
            installer.addEvents();
        },

        addEvents: function () {
            window.addEventListener('submit', function (event) {
                if (event.target.matches('#database-form')) {
                    event.preventDefault();
                    installer.database.install(event.target);
                }
            });
        },

        ajax: function (options) {
            let xhr = new XMLHttpRequest();
            let url = typeof options.url === 'string' ? options.url : window.location.href;
            let method = typeof options.method === 'string' ? options.method : 'GET';
            let data = typeof options.data === 'object' ? options.data : undefined;
            if (data && data.constructor.name === 'Object') {
                let objToQueryString = function (obj, prefix) {
                    let fields = [];
                    if(obj && Object.keys(obj).length) {
                        for(let i in obj) {
                            if(obj.hasOwnProperty(i)) {
                                if(typeof obj[i] === 'object') {
                                    fields.push(objToQueryString(obj[i], prefix ? prefix + '[' + i + ']' : i));
                                } else {
                                    fields.push((prefix ? prefix + '[' + i + ']' : i) + '=' + (typeof obj[i] === 'undefined' ? '' : encodeURIComponent(obj[i])));
                                }
                            }
                        }
                    } else if (prefix) {
                        fields.push(prefix + '=');
                    }
                    return fields.join('&');
                };
                data = objToQueryString(data);
                if(method === 'POST') {
                    let formData = new FormData();
                    data = data.split('&');
                    for(let i in data) {
                        if(data.hasOwnProperty(i)) {
                            let field = data[i].split('=');
                            formData.append(field[0], field[1]);
                        }
                    }
                    data = formData;
                } else if (data) {
                    let urlSplit = url.split('?');
                    url = urlSplit[0] + '?' + data + (urlSplit[1] ? '&' + urlSplit[1] : '');
                }
            }
            let xhrCallback = typeof options.xhr === 'function' ? options.xhr : undefined;
            let successCallback = typeof options.success === 'function' ? options.success : undefined;
            let errorCallback = typeof options.error === 'function' ? options.error : undefined;
            let uploadProgressCallback = typeof options.uploadProgress === 'function' ? options.uploadProgress : undefined;
            if (xhrCallback) {
                if (xhrCallback.apply(undefined, [xhr]) === false) {
                    return;
                }
            }
            if (uploadProgressCallback) {
                xhr.upload.addEventListener('progress', function (e) {
                    uploadProgressCallback.apply(undefined, [e]);
                }, false);
            }
            xhr.onreadystatechange = function(e) {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        if (successCallback) {
                            successCallback.apply(undefined, [xhr.responseText]);
                        }
                    } else if (xhr.status === 404) {
                        if (errorCallback) {
                            errorCallback.apply(undefined, [xhr]);
                        }
                    }
                }
            };
            xhr.open(method, url);
            xhr.send(data);
        },

        database: {
            form: null,
            currentStep: null,
            steps: [
                function () {
                    installer.database.form.querySelector('.form-progress').querySelector('.info').innerHTML = 'Checking details';
                    let data = new FormData(installer.database.form);
                    data.append('step', 'start');
                    installer.ajax({
                        url: installer.database.form.getAttribute('data-steps-url'),
                        method: 'POST',
                        data: data,
                        success: function (response) {
                            let data = JSON.parse(response);
                            if (data.status) {
                                installer.notify(data.message, 'success');
                                installer.database.step();
                            } else {
                                installer.database.currentStep = null;
                                installer.notify(data.message, 'danger', 0);
                                if (installer.database.form.querySelector('.form-progress').classList.contains('active')) {
                                    installer.database.form.querySelector('.form-progress').classList.remove('active');
                                }
                            }
                            if(data.redirect_url) {
                                setTimeout(function () {
                                    document.location.href = data.redirect_url;
                                }, 5000);
                            }
                        },
                        error: function () {
                            installer.database.currentStep = null;
                            installer.notify('Error in Connection. Please try again.', 'danger', 0);
                            if (installer.database.form.querySelector('.form-progress').classList.contains('active')) {
                                installer.database.form.querySelector('.form-progress').classList.remove('active');
                            }
                        }
                    });
                }, function () {
                    let actions = [];
                    let execAction = function (index) {
                        if(typeof index !== 'undefined') {
                            if(actions[index] === 'install') {
                                installer.database.form.querySelector('.form-progress').querySelector('.info').innerHTML = 'Installing core features';
                            }
                            if(actions[index] === 'update') {
                                installer.database.form.querySelector('.form-progress').querySelector('.info').innerHTML = 'Updating core settings';
                            }
                            if(actions[index] === 'upgrade') {
                                installer.database.form.querySelector('.form-progress').querySelector('.info').innerHTML = 'Upgrading core features';
                            }
                        }
                        installer.ajax({
                            url: installer.database.form.getAttribute('data-steps-url'),
                            method: 'POST',
                            data: {
                                step: 'core',
                                action: actions[index]
                            },
                            success: function (response) {
                                let data = JSON.parse(response);
                                if (data.status) {
                                    installer.notify(data.message, 'success');
                                    if(typeof index !== 'number') {
                                        actions = data.actions;
                                        if(typeof actions[0] === 'string') {
                                            execAction(0);
                                        } else {
                                            installer.database.step();
                                        }
                                    } else {
                                        installer.database.form.querySelector('.form-progress').querySelector('.percent').innerHTML = Math.round((((installer.database.currentStep === 0 ? 0 : (installer.database.currentStep / installer.database.steps.length)) * 100) + (((index + 1) / actions.length) * (100 / installer.database.steps.length)))) + '%';
                                        installer.database.form.querySelector('.form-progress').querySelector('.meter > span').style.width = (((installer.database.currentStep === 0 ? 0 : (installer.database.currentStep / installer.database.steps.length)) * 100) + (((index + 1) / actions.length) * (100 / installer.database.steps.length))) + '%';
                                        if (actions.length > index + 1) {
                                            execAction(index + 1);
                                        } else {
                                            installer.database.step();
                                        }
                                    }
                                } else {
                                    installer.database.currentStep = null;
                                    installer.notify(data.message, 'danger', 0);
                                    if (installer.database.form.querySelector('.form-progress').classList.contains('active')) {
                                        installer.database.form.querySelector('.form-progress').classList.remove('active');
                                    }
                                }
                                if(data.redirect_url) {
                                    setTimeout(function () {
                                        document.location.href = data.redirect_url;
                                    }, 5000);
                                }
                            },
                            error: function () {
                                installer.database.currentStep = null;
                                installer.notify('Error in Connection. Please try again.', 'danger', 0);
                                if (installer.database.form.querySelector('.form-progress').classList.contains('active')) {
                                    installer.database.form.querySelector('.form-progress').classList.remove('active');
                                }
                            }
                        });
                    };
                    execAction();
                }, function () {
                    installer.database.form.querySelector('.form-progress').querySelector('.info').innerHTML = 'Installing plugins';
                    let plugins = [];
                    let execPlugin = function (index) {
                        if(typeof index !== 'undefined') {
                            if(plugins[index]) {
                                installer.database.form.querySelector('.form-progress').querySelector('.info').innerHTML = 'Installing ' + plugins[index].replace(/^(.)|\s+(.)/g, function ($1) {return $1.toUpperCase()}) + ' plugin';
                            }
                        }
                        installer.ajax({
                            url: installer.database.form.getAttribute('data-steps-url'),
                            method: 'POST',
                            data: {
                                step: 'plugin',
                                plugin: plugins[index]
                            },
                            success: function (response) {
                                let data = JSON.parse(response);
                                if (data.status) {
                                    installer.notify(data.message, 'success');
                                    if(typeof index !== 'number') {
                                        plugins = data.plugins;
                                        if(typeof plugins[0] === 'string') {
                                            execPlugin(0);
                                        } else {
                                            installer.database.step();
                                        }
                                    } else {
                                        installer.database.form.querySelector('.form-progress').querySelector('.percent').innerHTML = Math.round((((installer.database.currentStep === 0 ? 0 : (installer.database.currentStep / installer.database.steps.length)) * 100) + (((index + 1) / plugins.length) * (100 / installer.database.steps.length)))) + '%';
                                        installer.database.form.querySelector('.form-progress').querySelector('.meter > span').style.width = (((installer.database.currentStep === 0 ? 0 : (installer.database.currentStep / installer.database.steps.length)) * 100) + (((index + 1) / plugins.length) * (100 / installer.database.steps.length))) + '%';
                                        if (plugins.length > index + 1) {
                                            execPlugin(index + 1);
                                        } else {
                                            installer.database.step();
                                        }
                                    }
                                } else {
                                    installer.database.currentStep = null;
                                    installer.notify(data.message, 'danger', 0);
                                    if (installer.database.form.querySelector('.form-progress').classList.contains('active')) {
                                        installer.database.form.querySelector('.form-progress').classList.remove('active');
                                    }
                                }
                                if(data.redirect_url) {
                                    setTimeout(function () {
                                        document.location.href = data.redirect_url;
                                    }, 5000);
                                }
                            },
                            error: function () {
                                installer.database.currentStep = null;
                                installer.notify('Error in Connection. Please try again.', 'danger', 0);
                                if (installer.database.form.querySelector('.form-progress').classList.contains('active')) {
                                    installer.database.form.querySelector('.form-progress').classList.remove('active');
                                }
                            }
                        });
                    };
                    execPlugin();
                }, function () {
                    installer.database.form.querySelector('.form-progress').querySelector('.info').innerHTML = 'Installing languages';
                    let languages = [];
                    let execLanguage = function (index) {
                        if(typeof index !== 'undefined') {
                            if(languages[index]) {
                                installer.database.form.querySelector('.form-progress').querySelector('.info').innerHTML = 'Installing ' + languages[index].replace(/^(.)|\s+(.)/g, function ($1) {return $1.toUpperCase()}) + ' language';
                            }
                        }
                        installer.ajax({
                            url: installer.database.form.getAttribute('data-steps-url'),
                            method: 'POST',
                            data: {
                                step: 'language',
                                language: languages[index]
                            },
                            success: function (response) {
                                let data = JSON.parse(response);
                                if (data.status) {
                                    installer.notify(data.message, 'success');
                                    if(typeof index !== 'number') {
                                        languages = data.languages;
                                        if(typeof languages[0] === 'string') {
                                            execLanguage(0);
                                        } else {
                                            installer.database.step();
                                        }
                                    } else {
                                        installer.database.form.querySelector('.form-progress').querySelector('.percent').innerHTML = Math.round((((installer.database.currentStep === 0 ? 0 : (installer.database.currentStep / installer.database.steps.length)) * 100) + (((index + 1) / languages.length) * (100 / installer.database.steps.length)))) + '%';
                                        installer.database.form.querySelector('.form-progress').querySelector('.meter > span').style.width = (((installer.database.currentStep === 0 ? 0 : (installer.database.currentStep / installer.database.steps.length)) * 100) + (((index + 1) / languages.length) * (100 / installer.database.steps.length))) + '%';
                                        if (languages.length > index + 1) {
                                            execLanguage(index + 1);
                                        } else {
                                            installer.database.step();
                                        }
                                    }
                                } else {
                                    installer.database.currentStep = null;
                                    installer.notify(data.message, 'danger', 0);
                                    if (installer.database.form.querySelector('.form-progress').classList.contains('active')) {
                                        installer.database.form.querySelector('.form-progress').classList.remove('active');
                                    }
                                }
                                if(data.redirect_url) {
                                    setTimeout(function () {
                                        document.location.href = data.redirect_url;
                                    }, 5000);
                                }
                            },
                            error: function () {
                                installer.database.currentStep = null;
                                installer.notify('Error in Connection. Please try again.', 'danger', 0);
                                if (installer.database.form.querySelector('.form-progress').classList.contains('active')) {
                                    installer.database.form.querySelector('.form-progress').classList.remove('active');
                                }
                            }
                        });
                    };
                    execLanguage();
                }, function () {
                    installer.database.form.querySelector('.form-progress').querySelector('.info').innerHTML = 'Finishing database installation';
                    installer.ajax({
                        url: installer.database.form.getAttribute('data-steps-url'),
                        method: 'POST',
                        data: {
                            step: 'finish'
                        },
                        success: function (response) {
                            let data = JSON.parse(response);
                            if (data.status) {
                                installer.notify(data.message, 'success');
                                installer.database.step();
                            } else {
                                installer.database.currentStep = null;
                                installer.notify(data.message, 'danger', 0);
                                if (installer.database.form.querySelector('.form-progress').classList.contains('active')) {
                                    installer.database.form.querySelector('.form-progress').classList.remove('active');
                                }
                            }
                            if(data.redirect_url) {
                                setTimeout(function () {
                                    document.location.href = data.redirect_url;
                                }, 5000);
                            }
                        },
                        error: function () {
                            installer.database.currentStep = null;
                            installer.notify('Error in Connection. Please try again.', 'danger', 0);
                            if (installer.database.form.querySelector('.form-progress').classList.contains('active')) {
                                installer.database.form.querySelector('.form-progress').classList.remove('active');
                            }
                        }
                    });
                }
            ],

            step: function () {
                if(installer.database.currentStep === null) {
                    installer.database.currentStep = 0;
                } else {
                    installer.database.currentStep++;
                }
                if(installer.database.steps.length > installer.database.currentStep) {
                    if(installer.database.currentStep === 0) {
                        installer.database.form.querySelector('.form-progress').querySelector('.percent').innerHTML = '1%';
                        installer.database.form.querySelector('.form-progress').querySelector('.meter > span').style.width = '1%';
                    } else {
                        installer.database.form.querySelector('.form-progress').querySelector('.percent').innerHTML = Math.round(((installer.database.currentStep === 0 ? 0 : (installer.database.currentStep / installer.database.steps.length)) * 100)) + '%';
                        installer.database.form.querySelector('.form-progress').querySelector('.meter > span').style.width = ((installer.database.currentStep === 0 ? 0 : (installer.database.currentStep / installer.database.steps.length)) * 100) + '%';
                    }
                    installer.database.steps[installer.database.currentStep].apply();
                } else {
                    installer.database.currentStep = null;
                    installer.database.form.querySelector('.form-progress').querySelector('.info').innerHTML = 'Database installation complete';
                    installer.database.form.querySelector('.form-progress').querySelector('.percent').innerHTML = '100%';
                    installer.database.form.querySelector('.form-progress').querySelector('.meter > span').style.width = '100%';
                }
            },

            install: function (form) {
                form = form || document.getElementById('database-form');
                installer.database.form = form;
                installer.database.form.querySelector('.form-progress').querySelector('.percent').innerHTML = '1%';
                installer.database.form.querySelector('.form-progress').querySelector('.meter > span').style.width = '1%';
                if (!installer.database.form.querySelector('.form-progress').classList.contains('active')) {
                    installer.database.form.querySelector('.form-progress').classList.add('active');
                }
                installer.database.step();
            },
        },

        notify: function (message, type, timeout) {
            timeout = timeout || 5000;
            let notifyContainer = document.getElementById('notify-container');
            let notifyPop = document.createElement('div');
            let notifyMessage = document.createElement('span');
            let notifyCloseButton = document.createElement('span');
            notifyPop.setAttribute('class', 'notify-pop' + (type ? ' ' + type : ''));
            notifyMessage.setAttribute('class', 'message');
            notifyMessage.innerHTML = message;
            notifyCloseButton.setAttribute('class', 'close-button');
            notifyCloseButton.innerHTML = 'X';
            notifyCloseButton.onclick = function (e) {
                e.preventDefault();
                notifyPop.remove();
            };
            notifyPop.appendChild(notifyMessage);
            notifyPop.appendChild(notifyCloseButton);
            notifyContainer.appendChild(notifyPop);
            if (timeout !== 0) {
                setTimeout(function () {
                    notifyPop.remove();
                }, timeout);
            }
        }
    };

    installer.init();
}

