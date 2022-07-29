/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var app = angular.module('administradora-figueira', ['ngRoute']);

app.config(['$routeProvider' , function($routeProvider){
    $routeProvider.when('/' , {
        templateUrl: 'plantillas/inicio.html'
    });
}]);