@extends('layouts.app')

@section('content')
<div class="container">
    <tasks-app></tasks-app>

    <!-- <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Welcome</div>

                <div class="panel-body">
                    Your Application's Landing Page.1
                </div>
            </div>

        </div>
    </div> -->
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
            }, (response) => {
                // 响应错误回调
            })
        },

        methods:{
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
                    } else {
                      console.log(response)
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
