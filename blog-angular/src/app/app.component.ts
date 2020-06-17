import { Component, OnInit, DoCheck } from '@angular/core';
import { UserService } from './services/user.services';
@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css'],
  providers: [UserService]
})
export class AppComponent {
  public title = 'blog-angular';
  public identity;
  public token;

  constructor(
  	public _userSerice: UserService
  	){
    this.loadUser();
  }

  ngOnInit(){
    console.log('Webapp correctly loaded');
  }

  ngDoCheck(){
    this.loadUser();
  }

  loadUser(){
    this.identity = this._userSerice.getIdentity();
    this.token = this._userSerice.getToken();
  }

}
