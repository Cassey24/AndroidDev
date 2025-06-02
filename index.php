<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogIn</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="style.css">

</head>
<body>
    <div class="container" id="signUp" style="display:none;">
        <h1 class="form-title">REGISTER</h1>
        <form method="post" action="reg.php">
        <td>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="FName" id="FName" placeholder="First Name" required>
                <label for="fName">First Name:</label>
            </div>
        </td>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="lName" id="lName" placeholder="Last Name" required>
                <label for="lName">Last Name:</label>
            </div>

            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="Email" required>
                <label for="email">Email:</label>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="password" required>
                <label for="password">Password:</label>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="cpassword" id="cpassword" placeholder="Cpassword" required>
                <label for="cpassword">Confirm Password:</label>
            </div>

            <div class="input-role">
                <i class="fas fa-user-tag"></i>
                <select name="role" id="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <input type="submit" class="btn" value="Sign Up" name="signUp">
        </form>

        <div class="icons">
            <i class="fab fa-google"></i>
            <i class="fab fa-facebook"></i>
        </div>

        <div class="links">
            <p>Already Have Account ?</p>
            <button id="signInButton">Sign In</button>
          </div>
        </div>
        
        <div class="container" id="signIn">
            <h1 class="form-title">SIGN IN</h1>
            <form method="post" action="signin.php">
              <div class="input-group">
                  <i class="fas fa-envelope"></i>
                  <input type="email" name="email" id="email" placeholder="email" required>
                  <label for="email">Email</label>
              </div>

              <div class="input-group">
                  <i class="fas fa-lock"></i>
                  <input type="password" name="password" id="password" placeholder="password" required>
                  <label for="password">Password</label>
              </div>

             <input type="submit" class="btn" value="Sign In" name="signIn">

            </form>

            <div class="icons">
              <i class="fab fa-google"></i>
              <i class="fab fa-facebook"></i>
            </div>

            <div class="links">
              <p>Don't have account yet?</p>
              <button id="signUpButton">Sign Up</button>
            </div>
          </div>

          <script src="script.js"></script>
</body>
</html>