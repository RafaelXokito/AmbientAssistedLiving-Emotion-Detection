package pt.ipleiria.estg.dei.ei.pi.AALBackend.entities;

import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.io.Serializable;

@NamedQueries({
        @NamedQuery(
                name = "getAllEmotions",
                query = "SELECT e FROM Emotion e"
        )
})

@Entity
@Table(name="EMOTION")
public class Emotion implements Serializable {
        @Id
        @GeneratedValue(strategy = GenerationType.IDENTITY)
        private Long id;
        @NotNull
        private String name;
        @NotNull
        private String group; // Positive, Negative, Neutral

        public Emotion() {
                this.name = "";
                this.group = "";
        }

        public Emotion(String name, String group) {
                this.name = name;
                this.group = group;
        }

        public Long getId() {
                return id;
        }

        public void setId(Long id) {
                this.id = id;
        }

        public String getName() {
                return name;
        }

        public void setName(String name) {
                this.name = name;
        }

        public String getGroup() {
                return group;
        }

        public void setGroup(String group) {
                this.group = group;
        }
}
