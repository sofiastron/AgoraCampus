
export function isLoggedIn() {
  return localStorage.getItem("enseignant_token") !== null;
}

export function login(token) {
  localStorage.setItem("enseignant_token", token);
}

export function logout() {
  localStorage.removeItem("enseignant_token");
}
