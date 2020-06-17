import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { User } from '../../models/user';
import { UserService } from '../../services/user.services';
//import { NgForm } from '@angular/forms';

@Component({
  selector: 'login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css'],
  providers: [UserService]
})
export class LoginComponent implements OnInit {
  public page_title: string;
  public user: User;
  public status: string;
  public token;
  public identity;
constructor(
		private _userService: UserService,
		private _router: Router,
		private _route: ActivatedRoute
	) { 
		  this.page_title = 'Identificate';
		  this.user = new User(1,'','','ROLE_USER','','','','');
    }

  ngOnInit() {
  	//This function its always active, it shut down when get parameter 'sure' 
  	this.logout();
  }

  onSubmit(form) {
	this._userService.signup(this.user).subscribe(
		response => {
			//Token
			if(response.status != 'error'){
				this.status= 'success';
				this.token = response;

				//Object user correctly identified
				this._userService.signup(this.user, true).subscribe(
					response => {
						this.identity = response;
			
						//Keep user identified

						console.log(this.token);
						console.log(this.identity);

						localStorage.setItem('token', this.token);
						localStorage.setItem('identity', JSON.stringify(this.identity));
						
						//GOES TO HOME PAGE
		  				this._router.navigateByUrl('/inicio');
					},
					error => {
						this.status = 'error';
						console.log(<any>error);
					}
				);
			}else{
				this.status = 'error';
			}
		},
		error => {
			this.status = 'error';
			console.log(<any>error);

		}

	);
  }
  logout(){
  		this._route.params.subscribe(params => {
  			let logout = +params['sure'];

  			if(logout == 1){
  				localStorage.removeItem('identity');
  				localStorage.removeItem('token');

  				this.identity = null; 
  				this.token = null;

  				//GOES TO HOME PAGE
  				//this._router.navigate(['inicio']);
  				this._router.navigateByUrl('/inicio');

  			}
  		}); 		

  }
}
