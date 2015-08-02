public class Person implements TemperatureWatcher {

	private String name;

	private Threshold threshold;

	private int alertState;

	public Person(String name) {
		this.name = name;
	}

	public void alert(TemperatureReading currentReading){
		System.out.println(name + " has been alerted: " + currentReading.getTemperature());
	}

	public Threshold getThreshold () {
		return threshold;
	}

	public void setThreshold(Threshold threshold) {
		this.threshold = threshold;
	}

	public int getAlertState (){
		return alertState;
	}

	public void setAlertState (int state) {
		alertState = state;
		System.out.println("State reset for " + name + ": " + state);
	}
}