@extends('layouts.app')

@section('content')
<div class="container">
    <tasks-app></tasks-app>
</div>

<template id="tasks-template"> 
    <form class="form-group" @submit="createTask">
        <input type="text" class="form-control" v-model="newTaskContent">
        <br />
        <button type="submit" class="btn btn-success btn-block">Create One Todo</button>
    </form>
    <br />
    <ul class="list-group">
        <li class="list-group-item" v-for="task in list | orderBy 'id' -1">
            <input v-model="task.completed" @change="onClickCheckbox(task)" type="checkbox">
            <span
                @click="editTask(task)"
                v-show="editedTask == null || editedTask.id != task.id">
                @{{ task.body }}
            </span>

            <input
                class="edit"
                type="text"
                v-model="task.body"
                v-show="editedTask != null && editedTask.id == task.id"
                v-task-focus="editedTask != null && editedTask.id == task.id"
                @blur="doneEdit(task)"
                @keyup.enter="doneEdit(task)"
                @keyup.esc="cancelEdit(task)"
            >
            <strong @click="deleteTask(task)">X</strong>
        </li>
    </ul>   
</template>

<script src="/javascript/vue.js"></script>
<script src="/javascript/vue-resource.js"></script>

<script>

    var TODO_STORAGE_KEY = 'todos'
    var USER_STORAGE_KEY = 'users'
    var KEEP_LOCAL_STORAGE = 'keep_local_storage'

    var todoStorage = {
        fetch: function () {
            var todos = JSON.parse(localStorage.getItem(TODO_STORAGE_KEY) || '[]')
            todos.forEach(function (todo, index) {
                todo.id = index
            })
            return todos
        },

        save: function (todos) {
            localStorage.setItem(TODO_STORAGE_KEY, JSON.stringify(todos))
        }
    }

    var userStorage = {
        // userStorage 用于保存用户的信息，可以保存一个标志位，这个标志位可以代表一个用户是否想要将本地的缓存上传到服务器。
        // 在用户下次打开网页的时候，就可以直接根据 userStorage 中的数据进行判断，而不需要再次询问用户
        fetch: function () {
            var users = JSON.parse(localStorage.getItem(USER_STORAGE_KEY) || '{}')
            return users
        },

        save: function (users) {
            localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(users))
        }
    }

    Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value')

    var resource = Vue.resource('/api/tasks{/id}')

    Vue.component('tasks-app',{
        template:'#tasks-template',

        data:function () {
            return {
                newTaskContent:'',
                list:[],
                editedTask: null,
            }
        },

        created:function () {
            var vm = this

            this.$http.get('/api/tasks', {user_id:{{$user_id}}}).then((response) => {
                vm.list = response.data

                // 从服务器端获取完用户的数据之后，再检查本地是否有缓存数据需要处理
                vm.checkLocalStorage()
            }, (response) => {
                // 响应错误回调
            })
        },

        methods:{
            deleteLocalTodoStorage: function () {
                todoStorage.save([])
            },

            checkLocalStorage: function () {
                var localTodos = todoStorage.fetch()
                var localUser = userStorage.fetch()
                var vm = this

                if (localTodos.length > 0) {
                    // 判断本地是否有保存的todo

                    userID = {{$user_id}}
                    userID = userID.toString()

                    if (localUser[userID] && localUser[userID][KEEP_LOCAL_STORAGE]) {
                        //该用户希望保存本地的缓存，不需要将缓存上传到服务器

                    } else {
                        //询问用户是否需要将本地缓存上传到服务器，并清除本地缓存
                        if(confirm("是否将本地的 Todo 记录保存到服务器？ 注意：这将删除本地的 Todo 记录")) {
                            //用户决定上传数据并清除本地缓存
                            localTodos.forEach(function (todo, index) {
                                var content = todo.body && todo.body.trim()
                                if (content) {
                                    vm.$http.post('/api/tasks',{user_id:{{$user_id}},body:content, completed:todo.completed},function (response){
                                        vm.list.push(response.task)
                                        vm.deleteLocalTodoStorage()
                                    }, (response) => {
                                        // 响应错误回调
                                    })
                                }
                            })
                        }
                        else {
                            //用户决定不上传缓存数据，则把用户的决策记录在 userStorage 中，这样用户下次再打开网页便不再询问了
                            if (!localUser[userID]) {
                                localUser[userID] = {}
                            }
                            localUser[userID][KEEP_LOCAL_STORAGE] = true

                            userStorage.save(localUser)
                        }
                    }
                }
            },

            editTask: function (task) {
                this.beforeEditCache = task.body
                this.editedTask = task
            },

            cancelEdit: function (task) {
                this.editedTodo = null
                task.body = this.beforeEditCache
            },

            doneEdit: function (task) {
                // 用户使用 enter 回车键结束编辑的时候，会先触发 keyup.enter ，然后再触发一次 blur
                if (!this.editedTask) {
                    return
                }
                this.editedTask = null

                if (!task.body.trim()){
                    this.deleteTask()
                    return
                }

                if (task.body != this.beforeEditCache ){
                    resource.update({id:task.id}, task).then(
                        (response) => {
                            if (response.data.message != "success") {
                                cancelEdit(task)
                            }
                    }, (response) => { })
                }
            },

            onClickCheckbox: function (task) {
                var vm = this;
                resource.update({id:task.id}, task).then((response) => {
                    if (response.data.message != "success") {
                        task.completed = !task.completed;
                    }
                }, (response) => {})
            },

            deleteTask:function (task) {
                var vm = this
                resource.delete({id:task.id}).then((response) => {
                    if ( response.data.message == "success" ){
                        vm.list.$remove(task)
                    }
                }, (response) => {})
            },

            createTask:function (e) {
                e.preventDefault()
                var content = this.newTaskContent && this.newTaskContent.trim()
                if (!content) {
                    return
                }
                var vm = this
                this.$http.post('/api/tasks',{user_id:{{$user_id}},body:content, completed:false},function (response){
                    vm.list.push(response.task)
                    this.newTaskContent = ''
                })
            }
        },

        directives: {
            'task-focus': function (value) {
                if (!value) {
                    return;
                }
                var el = this.el;
                Vue.nextTick(function () {
                    el.focus();
                });
            }
        }
    })

    new Vue({
        el:'body'
    });
</script>
@endsection
